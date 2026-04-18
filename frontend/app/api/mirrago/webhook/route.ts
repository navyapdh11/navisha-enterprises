import { NextRequest, NextResponse } from 'next/server';

export async function POST(req: NextRequest) {
  const body = await req.json();
  // Store result in cache/Redis for polling endpoint to read
  // For now, just acknowledge
  return NextResponse.json({ received: true, sessionId: body.session_id });
}
