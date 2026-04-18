// Utility functions for Navisha AI
window.Utils = {
    formatTime: (date = new Date()) => date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    randomItem: (arr) => arr[Math.floor(Math.random() * arr.length)],
    debounce: (fn, delay) => { let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); } },
    deepClone: (obj) => JSON.parse(JSON.stringify(obj)),
    generateId: () => Date.now() + '-' + Math.random().toString(36).substr(2, 8),
    sanitize: (text) => text.replace(/[<>]/g, ''),
    emojify: (text) => {
        const map = { namaste: 'नमस्ते 🙏', thank: 'धन्यवाद 🙏', dhaka: 'Dhaka 🧢', gunyu: 'Gunyu 👘', daura: 'Daura 👔' };
        for (let [k, v] of Object.entries(map)) text = text.replace(new RegExp(k, 'gi'), v);
        return text;
    }
};
