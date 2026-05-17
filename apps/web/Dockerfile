FROM node:22-alpine AS base
RUN corepack enable && corepack prepare pnpm@9 --activate
WORKDIR /app
COPY package.json* pnpm-lock.yaml* .npmrc* ./
RUN pnpm install --frozen-lockfile 2>/dev/null || pnpm install
COPY . .

FROM base AS dev
CMD ["pnpm", "dev", "--host", "0.0.0.0"]

FROM base AS build
RUN pnpm build
CMD ["node", ".output/server/index.mjs"]
