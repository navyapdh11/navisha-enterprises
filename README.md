# Navisha Enterprises - Cultural Commerce Intelligence

AI-powered Nepalese traditional clothing e-commerce platform with advanced search algorithms.

## Architecture
- **Backend**: Laravel 11 (PHP 8.3) + PostgreSQL + Redis
- **Frontend**: Next.js 15 + React 19 + TypeScript
- **AI Service**: Python FastAPI + LangGraph (MCTS, ToT, GoT, CoT, DFS, OASIS-IS)

## Quick Start
```bash
# 1. Copy env
cp .env.example .env

# 2. Start all services
docker-compose up -d

# 3. Run migrations
docker-compose exec backend php artisan migrate:fresh --seed

# 4. Start queue worker
docker-compose exec backend php artisan queue:work --queue=high,default,low

# 5. Verify
curl http://localhost:8000/api/health
curl http://localhost:3000
```

## Fixed Issues (Code Review)
| # | Issue | Fix |
|---|-------|-----|
| 1 | Idempotency key race condition | Order created first with unique key, app-level duplicate check |
| 2 | Async logging lost on queue failure | Synchronous fallback in ComplianceMiddleware |
| 3 | Redis Lua stock reservation | DB row locking with `lockForUpdate()` in transactions |
| 6 | Missing `quotas()` relationship | Added to User model + UserAIQuota model |
| 7 | Budget race condition | Atomic `Cache::lock()` in BudgetService |
| 9 | Missing `product_variants` table | Migration + model + FK from inventory |
| 10 | Order created before stock check | Pre-check + reserve before payment, rollback on failure |
| 12 | SSE not implemented | Polling endpoint `/api/mirrago/result/[sessionId]` |
| 13 | Missing `/festival` endpoint | Added to routes + frontend page |
| 15 | Mock AI products | LangGraph fetches from Laravel `/internal/recommendations` |
| 18 | `php artisan serve` not production | FrankenPHP Docker image |
| 19 | No health checks | Added to all services in docker-compose |
| 20 | Missing AI env vars | Added REDIS_HOST, DB connection to ai-service |

## Advanced Algorithms
- **DFS**: Depth-first search over thought trees
- **ToT**: Tree of Thoughts with branching reasoning
- **GoT**: Graph of Thoughts with interdependent nodes
- **CoT**: Chain of Thoughts step-by-step reasoning
- **MCTS**: Monte Carlo Tree Search for optimal response selection
- **OASIS-IS**: Adaptive exploration for agentic search

## Deployment
Production: AWS ECS with Terraform (scripts available on request)
