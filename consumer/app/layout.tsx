import type { Metadata } from 'next';
import type { ReactNode } from 'react';
import './globals.css';

export const metadata: Metadata = {
  title: {
    default: 'Hybrid Delivery — Consumer',
    template: '%s — Hybrid Delivery',
  },
  description:
    'A minimal Next.js consumer that renders articles from the hybrid WordPress delivery API via its versioned content contract.',
};

export default function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html lang="en">
      <body>{children}</body>
    </html>
  );
}
