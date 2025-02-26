import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  experimental: {
    turbo: {
      loaders: {},
    },
  },
  webpack: (config) => {
    config.resolve.alias = {
      ...(config.resolve.alias || {}),
      "react-server-dom-webpack/server.edge":
        "react-server-dom-webpack/server.browser",
    };
    return config;
  },
};

export default nextConfig;

