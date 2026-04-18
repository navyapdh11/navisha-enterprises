'use client';
import { useState } from 'react';

export default function CheckoutPage() {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  
  const handleCheckout = async (items: Array<{sku: string, quantity: number}>, total: number) => {
    setLoading(true);
    setError('');
    const idempotencyKey = crypto.randomUUID();
    
    try {
      const res = await fetch(`${process.env.API_URL || 'http://localhost:8000/api'}/checkout`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ items, total_amount: total, zone: 'kathmandu', idempotency_key: idempotencyKey }),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.error || 'Checkout failed');
      if (data.redirect_url) window.location.href = data.redirect_url;
    } catch (e: any) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <main style={{ padding: '2rem', maxWidth: 600, margin: '0 auto' }}>
      <h1>Checkout</h1>
      {error && <p style={{ color: 'red' }}>{error}</p>}
      <button onClick={() => handleCheckout([{ sku: 'DHAKA-TOP-001', quantity: 1 }], 1500)} disabled={loading}>
        {loading ? 'Processing...' : 'Place Order'}
      </button>
    </main>
  );
}
