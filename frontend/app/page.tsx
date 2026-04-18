'use client';
import { useEffect, useState } from 'react';

interface Product {
  id: number;
  name: string;
  description: string;
  price: string;
  image_url?: string;
}

export default function Home() {
  const [products, setProducts] = useState<Product[]>([]);
  useEffect(() => {
    fetch(`${process.env.API_URL || 'http://localhost:8000/api'}/products`)
      .then(r => r.json())
      .then(data => setProducts(data.data || []))
      .catch(() => setProducts([]));
  }, []);
  return (
    <main style={{ padding: '2rem', maxWidth: 1200, margin: '0 auto' }}>
      <h1>Navisha Enterprises</h1>
      <p>Preserving Nepalese Heritage • AI-Powered Cultural Intelligence</p>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '1.5rem', marginTop: '2rem' }}>
        {products.map(p => (
          <div key={p.id} style={{ border: '1px solid #e5e7eb', borderRadius: 12, padding: '1rem', transition: 'box-shadow 0.2s' }}>
            <h3>{p.name}</h3>
            <p>{p.description?.substring(0, 100)}</p>
            <p style={{ fontWeight: 'bold', color: '#D2691E' }}>NPR {p.price}</p>
          </div>
        ))}
      </div>
    </main>
  );
}
