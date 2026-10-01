import type { NextConfig } from 'next';

const backendOrigin = process.env.BACKEND_ORIGIN || process.env.NEXT_PUBLIC_API_ORIGIN || process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';

const nextConfig: NextConfig = {
  reactStrictMode: true,
  async rewrites() {
    return [{
      source: '/api/:path*',
      destination: `${backendOrigin.replace(/\/$/, '')}/api/:path*`,
    }];
  },
};

export default nextConfig;
