'use client';
import { useEffect, useState } from 'react';

export default function FestivalPage() {
  const [festivals, setFestivals] = useState<any[]>([]);
  useEffect(() => {
    fetch(`${process.env.API_URL || 'http://localhost:8000/api'}/festivals`)
      .then(r => r.json())
      .then(data => setFestivals(data.data || []))
      .catch(() => setFestivals([]));
  }, []);
  return (
    <main style={{ padding: '2rem' }}>
      <h1>Festival Collections</h1>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '1rem' }}>
        {festivals.map((f, i) => <div key={i} style={{ padding: '1rem', border: '1px solid #e5e7eb', borderRadius: 8 }}>{f.name}</div>)}
      </div>
    </main>
  );
}
