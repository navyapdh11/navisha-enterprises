// Navisha AI Super Agent with Advanced Search Algorithms (DFS, ToT, GoT, CoT, MCTS, OASIS-IS)
class NavishaAIAgent {
    constructor() {
        this.agentActive = false;
        this.conversationHistory = [];
        this.systemMetrics = { interactions: 0, culturalQueries: 0, conversions: 0, userSatisfaction: 85, activeUsers: 1, avgSessionTime: 0 };
        this.culturalKnowledge = this.loadCulturalKnowledge();
        this.initEventListeners();
        this.loadFromStorage();
        this.monitorBehavior();
    }

    loadCulturalKnowledge() {
        return {
            traditional_clothing: [
                { id: 1, name: 'Dhaka Topi', description: 'Traditional Nepalese hat made from authentic Dhaka fabric, symbolizing national pride.', cultural_significance: 'Worn during festivals and national events', regions: ['Kathmandu', 'Pokhara'], occasions: ['Dashain', 'Tihar'], price: '$45.99', materials: ['Cotton', 'Silk'] },
                { id: 2, name: 'Gunyu Cholo', description: 'Traditional Newari women\'s attire with intricate embroidery.', cultural_significance: 'Represents Newari heritage', regions: ['Kathmandu Valley'], occasions: ['Weddings'], price: '$89.99', materials: ['Silk', 'Gold Thread'] },
                { id: 3, name: 'Daura Suruwal', description: 'Official national dress of Nepal for men.', cultural_significance: 'National symbol', regions: ['All Nepal'], occasions: ['Official Events'], price: '$75.50', materials: ['Cotton'] }
            ],
            cultural_stories: [
                { id: 1, title: 'The Art of Dhaka Weaving', story: 'Dhaka fabric weaving dates back over 500 years. Each pattern encodes family lineages.', cultural_insight: 'Colors represent bravery, wisdom, prosperity.' },
                { id: 2, title: 'Newari Craftsmanship', story: 'Newari artisans preserve ancient weaving techniques passed down for centuries.', cultural_insight: 'Embroidery depicts religious symbols and nature.' }
            ],
            shopping_guidance: { sizing: 'Traditional fit is looser; consult size chart.', authenticity: 'RWA verified with digital certificate.', shipping: 'Worldwide shipping 7-14 days.' }
        };
    }

    initEventListeners() {
        document.getElementById('agentToggle')?.addEventListener('click', () => this.toggleAgent());
        document.getElementById('closeBtn')?.addEventListener('click', () => this.hideAgent());
        document.getElementById('minimizeBtn')?.addEventListener('click', () => this.hideAgent());
        document.getElementById('sendBtn')?.addEventListener('click', () => this.sendMessage());
        document.getElementById('messageInput')?.addEventListener('keypress', (e) => e.key === 'Enter' && this.sendMessage());
        document.querySelectorAll('.quick-action').forEach(btn => btn.addEventListener('click', (e) => this.handleQuickAction(e.target.dataset.action)));
        document.getElementById('openAgentBtn')?.addEventListener('click', () => this.showAgent());
        document.getElementById('culturalStoryBtn')?.addEventListener('click', () => this.askAboutCulture());
        document.getElementById('adminBtn')?.addEventListener('click', () => this.showAdminPanel());
        document.getElementById('resetSystemBtn')?.addEventListener('click', () => this.resetSystem());
    }

    toggleAgent() { this.agentActive = !this.agentActive; const w = document.getElementById('agentWindow'); const b = document.getElementById('agentToggle'); this.agentActive ? (w.classList.add('active'), b.classList.add('active'), document.getElementById('messageInput').focus()) : (w.classList.remove('active'), b.classList.remove('active')); this.trackInteraction('agent_toggle'); }
    showAgent() { if (!this.agentActive) this.toggleAgent(); }
    hideAgent() { if (this.agentActive) this.toggleAgent(); }

    sendMessage() {
        const input = document.getElementById('messageInput');
        const msg = input.value.trim();
        if (!msg) return;
        this.addMessage(msg, 'user');
        input.value = '';
        this.showTypingIndicator();
        setTimeout(() => {
            const response = this.generateAdvancedResponse(msg);
            this.hideTypingIndicator();
            this.addMessage(response, 'ai');
            this.trackInteraction('user_message', { length: msg.length });
        }, 500);
    }

    addMessage(content, sender) {
        const container = document.getElementById('messagesContainer');
        const div = document.createElement('div');
        div.className = `message ${sender}`;
        div.innerHTML = `<div class="message-content">${sender === 'ai' ? this.enhanceWithCultural(content) : Utils.emojify(Utils.sanitize(content))}</div><div class="message-time">${Utils.formatTime()}</div>`;
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
        this.conversationHistory.push({ sender, content, timestamp: new Date().toISOString() });
        this.saveToStorage();
        if (sender === 'user') this.systemMetrics.interactions++;
        this.updateSystemStatus();
    }

    enhanceWithCultural(text) {
        if (Math.random() < 0.25 && /nepal|cultural|traditional|heritage/i.test(text)) {
            const insights = ['In Nepalese culture, clothing colors have deep meanings.', 'Dhaka weaving patterns tell family stories.', 'Newari embroidery symbolizes nature and spirituality.'];
            text += `<div class="cultural-insight"><i class="fas fa-seedling"></i> ${Utils.randomItem(insights)}</div>`;
        }
        return Utils.emojify(text);
    }

    // ---------- Advanced Search & Reasoning ----------
    // Depth‑First Search (DFS) over thought tree
    dfsThoughtTree(query, depth = 3) {
        const stack = [{ node: query, path: [] }];
        const results = [];
        while (stack.length) {
            const { node, path } = stack.pop();
            if (path.length >= depth) continue;
            const children = this.generateThoughtChildren(node);
            for (let child of children) {
                const newPath = [...path, child];
                if (child.includes('answer') || child.includes('product')) results.push(newPath);
                stack.push({ node: child, path: newPath });
            }
        }
        return results.length ? results[0] : [query];
    }

    generateThoughtChildren(thought) {
        // Simple expansion: add keywords or context
        let children = [];
        if (/dhaka|topi/i.test(thought)) children.push('Dhaka Topi is a traditional hat.');
        if (/gunyu|cholo/i.test(thought)) children.push('Gunyu Cholo is Newari women\'s dress.');
        if (/daura|suruwal/i.test(thought)) children.push('Daura Suruwal is national dress.');
        if (children.length === 0) children.push(`${thought} is part of Nepalese cultural heritage.`);
        return children;
    }

    // Tree of Thoughts (ToT) – generate multiple reasoning paths
    treeOfThoughts(query, branches = 3) {
        let thoughts = [query];
        for (let i = 0; i < branches; i++) {
            let newThoughts = [];
            for (let t of thoughts) {
                newThoughts.push(...this.generateThoughtChildren(t));
            }
            thoughts = newThoughts.slice(0, branches);
        }
        return thoughts;
    }

    // Graph of Thoughts (GoT) – link concepts
    graphOfThoughts(query) {
        const graph = new Map();
        const nodes = [query, ...this.generateThoughtChildren(query)];
        for (let node of nodes) {
            let edges = this.generateThoughtChildren(node);
            graph.set(node, edges);
        }
        return graph;
    }

    // Chain of Thoughts (CoT) – step‑by‑step reasoning
    chainOfThoughts(query) {
        let chain = [`Initial query: ${query}`];
        let current = query;
        for (let step = 0; step < 3; step++) {
            let next = this.generateThoughtChildren(current)[0];
            if (!next) break;
            chain.push(`Step ${step+1}: ${next}`);
            current = next;
        }
        return chain;
    }

    // Monte Carlo Tree Search (MCTS) for optimal response
    mctsSearch(query, iterations = 10) {
        class MCTSNode {
            constructor(state, parent = null) { this.state = state; this.parent = parent; this.children = []; this.visits = 0; this.value = 0; }
        }
        const root = new MCTSNode(query);
        for (let i = 0; i < iterations; i++) {
            let node = root;
            // Selection
            while (node.children.length > 0) {
                node = node.children.reduce((best, n) => (n.value / (n.visits + 1e-6) > best.value / (best.visits + 1e-6) ? n : best), node.children[0]);
            }
            // Expansion
            let possible = this.generateThoughtChildren(node.state);
            for (let p of possible) {
                let child = new MCTSNode(p, node);
                node.children.push(child);
            }
            // Simulation (rollout)
            let rolloutState = node.state;
            for (let s = 0; s < 3; s++) {
                let next = this.generateThoughtChildren(rolloutState)[0];
                if (!next) break;
                rolloutState = next;
            }
            let reward = rolloutState.includes('answer') ? 1 : (rolloutState.includes('product') ? 0.8 : 0.3);
            // Backpropagation
            let back = node;
            while (back) {
                back.visits++;
                back.value += reward;
                back = back.parent;
            }
        }
        // Best child
        let best = root.children.reduce((a,b) => (a.value / a.visits > b.value / b.visits ? a : b), root.children[0]);
        return best ? best.state : query;
    }

    // OASIS‑IS Agentic Search (adaptive exploration)
    oasisIsSearch(query, depth = 3) {
        let bestPath = [];
        let bestScore = -Infinity;
        const explore = (state, path, level) => {
            if (level >= depth) {
                let score = path.filter(p => /cultural|traditional|authentic/i.test(p)).length;
                if (score > bestScore) { bestScore = score; bestPath = [...path]; }
                return;
            }
            let children = this.generateThoughtChildren(state);
            for (let child of children) {
                explore(child, [...path, child], level + 1);
            }
        };
        explore(query, [query], 0);
        return bestPath.length ? bestPath.join(' → ') : query;
    }

    // Unified response generator using all techniques
    generateAdvancedResponse(userMsg) {
        const lowerMsg = userMsg.toLowerCase();
        // Use MCTS to decide best reasoning path
        const mctsResult = this.mctsSearch(lowerMsg);
        const totPaths = this.treeOfThoughts(lowerMsg);
        const cotChain = this.chainOfThoughts(lowerMsg);
        const oasisResult = this.oasisIsSearch(lowerMsg);
        // Combine insights
        let finalAnswer = '';
        if (/product|clothing|dhaka|gunyu|daura/i.test(lowerMsg)) {
            const product = this.culturalKnowledge.traditional_clothing.find(p => lowerMsg.includes(p.name.toLowerCase()));
            if (product) finalAnswer = `${product.name}: ${product.description} Price: ${product.price}. Cultural significance: ${product.cultural_significance}.`;
            else finalAnswer = `Our traditional collection includes Dhaka Topi, Gunyu Cholo, Daura Suruwal. Which interests you?`;
        } else if (/story|culture|heritage/i.test(lowerMsg)) {
            const story = Utils.randomItem(this.culturalKnowledge.cultural_stories);
            finalAnswer = `${story.title}\n${story.story}\nInsight: ${story.cultural_insight}`;
        } else if (/sizing|size|fit/i.test(lowerMsg)) {
            finalAnswer = this.culturalKnowledge.shopping_guidance.sizing;
        } else if (/authenticity|rwa/i.test(lowerMsg)) {
            finalAnswer = this.culturalKnowledge.shopping_guidance.authenticity;
        } else if (/shipping|delivery/i.test(lowerMsg)) {
            finalAnswer = this.culturalKnowledge.shopping_guidance.shipping;
        } else {
            finalAnswer = `I used advanced reasoning (MCTS, ToT, CoT, OASIS) to understand you. ${mctsResult}\n\n${cotChain.join(' ')}\n\nRecommendation: ${oasisResult}`;
        }
        return finalAnswer;
    }

    handleQuickAction(action) {
        let msg = '';
        switch(action) {
            case 'products': msg = 'Tell me about your traditional clothing products.'; break;
            case 'stories': msg = 'Share a cultural story.'; break;
            case 'sizing': msg = 'What is the sizing guide?'; break;
            case 'authenticity': msg = 'How do you verify authenticity?'; break;
            case 'shipping': msg = 'Shipping information please.'; break;
            default: return;
        }
        this.addMessage(msg, 'user');
        setTimeout(() => {
            const resp = this.generateAdvancedResponse(msg);
            this.addMessage(resp, 'ai');
        }, 300);
    }

    askAboutCulture() { this.showAgent(); setTimeout(() => { this.addMessage("Tell me a cultural story", 'user'); setTimeout(() => this.addMessage(this.generateAdvancedResponse("cultural story"), 'ai'), 500); }, 300); }
    showAdminPanel() { document.getElementById('adminPanel').classList.add('active'); document.getElementById('overlay').classList.add('active'); this.loadAdminData(); }
    hideAdminPanel() { document.getElementById('adminPanel').classList.remove('active'); document.getElementById('overlay').classList.remove('active'); }
    loadAdminData() { this.updateSystemStatus(); this.renderAnalytics(); this.renderConversations(); this.renderProducts(); this.renderSearchTab(); }
    renderAnalytics() { const container = document.getElementById('analyticsTab'); if(container) container.innerHTML = `<div class="stats-grid">${Object.entries(this.systemMetrics).map(([k,v])=>`<div class="stat-card"><div class="stat-value">${v}</div><div class="stat-label">${k}</div></div>`).join('')}</div>`; }
    renderConversations() { const container = document.getElementById('conversationsTab'); if(container) container.innerHTML = this.conversationHistory.slice(-20).reverse().map(m => `<div><strong>${m.sender}:</strong> ${m.content.substring(0,100)}</div>`).join('') || '<p>No conversations</p>'; }
    renderProducts() { const container = document.getElementById('productsTab'); if(container) container.innerHTML = this.culturalKnowledge.traditional_clothing.map(p => `<div style="border:1px solid #ccc;padding:10px;margin:5px;"><b>${p.name}</b> ${p.price}<br>${p.description}</div>`).join(''); }
    renderSearchTab() { const container = document.getElementById('searchTab'); if(container) container.innerHTML = `<h4>OASIS‑IS Agentic Search Demo</h4><input id="searchQuery" placeholder="Try 'Dhaka Topi'"><button onclick="window.navishaAI.demoSearch()">Search</button><div id="searchResult"></div>`; }
    demoSearch() { const q = document.getElementById('searchQuery')?.value || 'traditional'; const result = this.oasisIsSearch(q); document.getElementById('searchResult').innerHTML = `<p>Search path: ${result}</p>`; }
    updateSystemStatus() { const div = document.getElementById('systemStatus'); if(div) div.innerHTML = `<div class="stats-grid">${Object.entries(this.systemMetrics).slice(0,4).map(([k,v])=>`<div class="stat-card"><div class="stat-value">${v}</div><div class="stat-label">${k}</div></div>`).join('')}</div>`; }
    trackInteraction(type, data={}) { this.systemMetrics.interactions++; const logs = JSON.parse(localStorage.getItem('navisha_interactions')||'[]'); logs.push({type,data,timestamp:new Date().toISOString()}); localStorage.setItem('navisha_interactions',JSON.stringify(logs.slice(-100))); this.updateSystemStatus(); }
    saveToStorage() { localStorage.setItem('navisha_system_data', JSON.stringify({ conversationHistory: this.conversationHistory.slice(-100), systemMetrics: this.systemMetrics, lastUpdated: new Date().toISOString() })); }
    loadFromStorage() { try { const data = JSON.parse(localStorage.getItem('navisha_system_data')); if(data) { this.conversationHistory = data.conversationHistory || []; this.systemMetrics = data.systemMetrics || this.systemMetrics; } } catch(e){} }
    resetSystem() { if(confirm('Reset all data?')) { this.conversationHistory = []; this.systemMetrics = { interactions:0, culturalQueries:0, conversions:0, userSatisfaction:85, activeUsers:1, avgSessionTime:0 }; localStorage.clear(); location.reload(); } }
    monitorBehavior() { setInterval(() => { this.systemMetrics.avgSessionTime++; this.updateSystemStatus(); }, 60000); }
    showTypingIndicator() { const c = document.getElementById('messagesContainer'); const d = document.createElement('div'); d.className = 'typing-indicator'; d.id = 'typingIndicator'; d.innerHTML = '<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>'; c.appendChild(d); c.scrollTop = c.scrollHeight; }
    hideTypingIndicator() { document.getElementById('typingIndicator')?.remove(); }
}
