import type { Metadata } from 'next';
export const metadata: Metadata = {
  title: 'Navisha Enterprises',
  description: 'Cultural Commerce Intelligence',
};
export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <body style={{ fontFamily: 'system-ui, sans-serif' }}>{children}</body>
    </html>
  );
}
