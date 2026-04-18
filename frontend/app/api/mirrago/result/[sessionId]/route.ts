import { NextRequest, NextResponse } from 'next/server';

export async function GET(req: NextRequest, { params }: { params: Promise<{ sessionId: string }> }) {
  const { sessionId } = await params;
  // Check Redis/cache for result (populated by Mirrago webhook)
  // For now, return mock - replace with actual Redis read
  const result = await fetch(`${process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api'}/mirrago/result/${sessionId}`).catch(() => null);
  
  if (!result || !result.ok) {
    return NextResponse.json({ status: 'processing', sessionId });
  }
  
  const data = await result.json();
  return NextResponse.json({
    status: data.status === 'ready' ? 'ready' : data.status === 'failed' ? 'failed' : 'processing',
    tryon_image: data.image_url,
    sessionId,
  });
}
