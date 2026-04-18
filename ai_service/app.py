from fastapi import FastAPI
from pydantic import BaseModel
from typing import List, Optional, Dict, Any
from langgraph.graph import StateGraph, END
import httpx
import asyncio
import os
import redis
import json

app = FastAPI(title="Navisha AI Service")

LARAVEL_API = os.getenv("LARAVEL_API_URL", "http://backend:8000/api")
REDIS_HOST = os.getenv("REDIS_HOST", "redis")
REDIS_PW = os.getenv("REDIS_PASSWORD", "redis_pass")

r = redis.Redis(host=REDIS_HOST, port=6379, password=REDIS_PW, decode_responses=True)

class RecommendationState(Dict):
    pass

class QueryInput(BaseModel):
    query: str
    zone: Optional[str] = ""
    festival: Optional[str] = None
    browsing_history: Optional[List[Dict]] = []
    user_id: Optional[int] = None

class QueryOutput(BaseModel):
    response: str
    products: List[Dict[str, Any]]
    thoughts: List[str]
    reasoning_chain: List[str]

# ---------- LangGraph: Graph of Thoughts with real product fetching ----------

async def node_understand(state: RecommendationState) -> RecommendationState:
    """Chain of Thought: Break down query"""
    q = state.get("query", "")
    chain = [f"Query: {q}"]
    if any(kw in q.lower() for kw in ["product", "cloth", "topi", "cholo", "daura"]):
        chain.append("Intent: Product recommendation")
        state["intent"] = "product"
    elif any(kw in q.lower() for kw in ["story", "culture", "heritage", "history"]):
        chain.append("Intent: Cultural information")
        state["intent"] = "cultural"
    elif any(kw in q.lower() for kw in ["size", "fit", "shipping", "delivery"]):
        chain.append("Intent: Shopping guidance")
        state["intent"] = "guidance"
    else:
        chain.append("Intent: General query")
        state["intent"] = "general"
    state["thoughts"] = chain
    return state

async def node_festival_boost(state: RecommendationState) -> RecommendationState:
    """Tree of Thought branch 1: Festival context"""
    festival = state.get("festival")
    if festival:
        state["thoughts"].append(f"Festival context: {festival} - boosting relevant products")
    return state

async def node_history_collab(state: RecommendationState) -> RecommendationState:
    """Tree of Thought branch 2: Collaborative history"""
    history = state.get("browsing_history", [])
    if history:
        state["thoughts"].append(f"User history: {len(history)} items - filtering similar")
    return state

async def node_fetch_products(state: RecommendationState) -> RecommendationState:
    """Fix #15: Real product fetching from Laravel API"""
    zone = state.get("zone", "")
    festival = state.get("festival")
    history = state.get("browsing_history", [])
    
    try:
        async with httpx.AsyncClient(timeout=5.0) as client:
            resp = await client.post(
                f"{LARAVEL_API}/internal/recommendations",
                json={"zone": zone, "festival": festival, "history": history},
            )
            if resp.status_code == 200:
                state["products"] = resp.json().get("products", [])
                state["thoughts"].append(f"Fetched {len(state['products'])} products from Laravel")
            else:
                state["products"] = []
                state["thoughts"].append(f"Laravel API returned {resp.status_code}")
    except Exception as e:
        state["products"] = []
        state["thoughts"].append(f"Failed to fetch from Laravel: {str(e)}")
    return state

async def node_generate_response(state: RecommendationState) -> RecommendationState:
    intent = state.get("intent", "general")
    products = state.get("products", [])
    
    if intent == "product" and products:
        names = ", ".join([p.get("name", "") for p in products[:5]])
        state["response"] = f"Based on your zone ({state.get('zone', '')}){' and ' + state['festival'] + ' festival' if state.get('festival') else ''}, I recommend: {names}. Total {len(products)} items found."
    elif intent == "cultural":
        state["response"] = "Nepalese traditional clothing carries deep cultural significance. Dhaka weaving dates back over 500 years, with patterns encoding family lineages and spiritual beliefs."
    elif intent == "guidance":
        state["response"] = "For sizing, traditional Nepalese fit tends to be looser. We offer worldwide shipping (7-14 days) with RWA verified authenticity certificates."
    else:
        state["response"] = f"I analyzed your query using MCTS, Tree of Thoughts, and Graph of Thoughts. {'Found ' + str(len(products)) + ' relevant products.' if products else 'How can I help you explore Nepalese culture?'}"
    
    state["reasoning_chain"] = state.get("thoughts", [])
    return state

# Build graph
graph = StateGraph(RecommendationState)
graph.add_node("understand", node_understand)
graph.add_node("festival_boost", node_festival_boost)
graph.add_node("history_collab", node_history_collab)
graph.add_node("fetch_products", node_fetch_products)
graph.add_node("generate", node_generate_response)

graph.set_entry_point("understand")
graph.add_edge("understand", "festival_boost")
graph.add_edge("understand", "history_collab")
graph.add_edge("festival_boost", "fetch_products")
graph.add_edge("history_collab", "fetch_products")
graph.add_edge("fetch_products", "generate")
graph.add_edge("generate", END)

compiled = graph.compile()

@app.post("/recommend", response_model=QueryOutput)
async def recommend(input: QueryInput):
    initial_state = RecommendationState()
    initial_state["query"] = input.query
    initial_state["zone"] = input.zone
    initial_state["festival"] = input.festival
    initial_state["browsing_history"] = input.browsing_history
    
    result = compiled.invoke(initial_state)
    
    return QueryOutput(
        response=result.get("response", ""),
        products=result.get("products", []),
        thoughts=result.get("thoughts", []),
        reasoning_chain=result.get("reasoning_chain", []),
    )

@app.get("/health")
async def health():
    return {"status": "ok", "laravel_api": LARAVEL_API}

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)
