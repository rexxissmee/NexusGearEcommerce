import path from 'node:path'
/** @type {import('next').NextConfig} */
const nextConfig = {
  eslint: {
    ignoreDuringBuilds: true,
  },
  typescript: {
    ignoreBuildErrors: true,
  },
  allowedDevOrigins: [
    '',
  ],
  images: {
    unoptimized: true,
  },
  async rewrites() {
    const projectBase = path.basename(process.cwd())
    return [
      {
        source: '/public/api/:path*',
        destination: `http://localhost/${projectBase}/public/api/:path*`,
      },
    ]
  },
}

export default nextConfig
