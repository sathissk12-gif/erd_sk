# 🧠 SK JARVIS — Implementation Plan

## Phase 1 (Current) — Voice + Quick Wins

| # | Task | File(s) | Status |
|---|------|---------|--------|
| 1 | Add Voice Output (TTS) — speaker toggle + auto-speak | [`ai_chat.php`](../ai_chat.php) | ⏳ |
| 2 | Wire Voice Input — mic button to SpeechRecognition (Tamil) | [`ai_chat.php`](../ai_chat.php) | ✅ Already exists |
| 3 | Add Proactive Greeting — show today's stats on load | [`ai_chat.php`](../ai_chat.php) | ⏳ |
| 4 | Create Action Executor API | [`api_jarvis_action.php`](../api_jarvis_action.php) (NEW) | ⏳ |
| 5 | Add Action Confirmation Dialog in chat UI | [`ai_chat.php`](../ai_chat.php) | ⏳ |
| 6 | Modify Gemini API to return action intents | [`api_gemini.php`](../api_gemini.php) | ⏳ |
| 7 | Modify DeepSeek API similarly | [`api_deepseek.php`](../api_deepseek.php) | ⏳ |

## Phase 2 — Proactive Intelligence

| # | Task | File(s) | Status |
|---|------|---------|--------|
| 1 | Create cron engine for scheduled alerts | [`jarvis_cron.php`](../jarvis_cron.php) (NEW) | ⏳ |
| 2 | Add anomaly detection (sales drops, stock aging) | [`api_jarvis_analytics.php`](../api_jarvis_analytics.php) (NEW) | ⏳ |
| 3 | Add time-of-day smart suggestions | [`ai_chat.php`](../ai_chat.php) | ⏳ |

## Phase 3 — Multi-Channel

| # | Task | File(s) | Status |
|---|------|---------|--------|
| 1 | Connect WhatsApp webhook to Jarvis AI | [`meta_webhook.php`](../meta_webhook.php) | ⏳ |
| 2 | Enable FCM smart push alerts from cron | [`jarvis_cron.php`](../jarvis_cron.php) | ⏳ |

## Phase 4 — Advanced Agents

| # | Task | File(s) | Status |
|---|------|---------|--------|
| 1 | Create Orchestrator API | [`api_jarvis_orchestrator.php`](../api_jarvis_orchestrator.php) (NEW) | ⏳ |
| 2 | Create Memory/Learning layer | [`jarvis_memory.php`](../jarvis_memory.php) (NEW) | ⏳ |
| 3 | Add inline chart rendering | [`ai_chat.php`](../ai_chat.php) | ⏳ |
