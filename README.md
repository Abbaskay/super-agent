# super-agent

Super Agent (Auth Hub) — Central authentication hub with JWT-based SSO for all sub-agents.

## Overview

This repository contains both the backend and frontend for the **Super Agent (Auth Hub)** — one of the five agents in the Hyperzod multi-agent AI content suite.

### Tech Stack
- **Backend**: Laravel (PHP)
- **Frontend**: Vue 3 + Vite
- **AI Integration**: Dify (DeepSeek model)
- **Auth**: JWT-based SSO via Super Agent
- **Database**: SQLite

### Project Structure
```
super-agent/
├── super-agent-backend/              # Laravel backend
│   ├── app/
│   ├── config/
│   ├── routes/
│   ├── database/
│   └── ...
├── super-agent-frontend/              # Vue 3 frontend
│   ├── src/
│   │   ├── components/
│   │   ├── views/
│   │   ├── services/
│   │   └── store/
│   ├── vite.config.js
│   └── ...

└── README.md
```

## Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- npm
- SQLite

## Setup

### 1. Backend

```bash
cd super-agent-backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve --port=8000
```

### 2. Frontend

```bash
cd super-agent-frontend
npm install
npm run dev
```

## Auth / SSO

This agent uses JWT-based SSO via the **Super Agent**. Login is handled through the Super Agent frontend which redirects here with a signed JWT cookie.

| Credential | Value |
|------------|-------|
| Login URL | http://localhost:5173 |
| Email | admin@superagent.com |
| Password | admin123 |

### JWT Configuration

All agents share the same JWT secret:

```
JWT_SECRET=change-this-to-a-secure-secret-in-production
JWT_COOKIE=super_agent_token
```

## Ports

| Service  | Port |
|----------|------|
| Backend  | 8000 |
| Frontend | 5173 |

## Dify Configuration

Set the following in `.env`:

```
DIFY_API_URL=http://localhost/v1/workflows/run
```

## Agent Suite

The Super Agent (Auth Hub) is part of a 5-agent suite:

| Agent | Backend | Frontend |
|-------|---------|----------|
| Super Agent | :8000 | :5173 |
| Doc Agent | :8002 | :5174 |
| Slides Agent | :8003 | :5175 |
| Fact-Check Agent | :8001 | :5176 |
| Excel Sheet Agent | :8004 | :5177 |
