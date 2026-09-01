---
title: Getting Started with AI Adoption in GitKraken Insights
description: Get oriented in the AI Adoption dashboard in GitKraken Insights, with quickstarts for executives, engineering leaders, team leads, and admins, plus a guided walkthrough of your first dashboard.
product: GitKraken Insights
content_type: how-to
audience: all
plan_required: GitKraken Insights
integrations: [GitHub, GitHub Enterprise Server, GitLab, GitLab Self-Managed, Bitbucket, Azure DevOps, Azure DevOps Server, Claude Code, Codex, Cursor, Jira Cloud]
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: purple
    card_description: Orientation, first dashboard, and role-specific quickstarts
    card_icon: compass
    nav_category: getting-started
    nav_label: Getting Started
    nav_order: 0
    page_type: index
---
<kbd>Last updated: September 2026</kbd>

GitKraken Insights is the dashboard your engineering organization uses to see — in one place — how AI tools, code delivery, and team capacity actually interact. This section gets you oriented in about ten minutes.

---

## What this product does, in one sentence

It connects your git provider (GitHub, Bitbucket, Azure DevOps, GitLab, or a self-hosted GitHub Enterprise Server, Azure DevOps Server, or GitLab Self-Managed instance), your AI coding tool telemetry (Claude Code, Codex, Cursor), your Jira customer bugs, and your BambooHR PTO calendar, and produces a small set of trustworthy numbers for engineering leadership.

## What this product does _not_ do

* It does not rank or compare individual developers against each other for performance reviews. The scoring framework is designed for _trend reading_ and _cohort comparison_, not for slotting people on a curve.
* It does not replace your standup, your retro, or your one-on-ones. It surfaces patterns that should become conversations.
* It does not produce real-time alerts. The numbers refresh on a window (default 14 days) and are designed for weekly / monthly / quarterly review.

---

## Three pages that matter most

If you take only one thing from this guide, take this: there are three pages that will answer 80% of the questions you'll have on a given day.

| Page | When to use it |
| --- | --- |
| **/teams** _(the default landing)_ | Weekly leadership review. Team-by-team adoption and delivery, side by side. |
| **/developers** | When you want to drill into individuals: who's a Power User, who's struggling to adopt, who needs onboarding. |
| **/executive** | Monthly or quarterly reports up the chain. Pre-shaped for execs with hero KPIs and trend lines. |

The other pages (/impact, /ai-impact, /comparison, /flow-delivery, /capex, /data, /board-metrics, /settings) are deep-dive surfaces for specific questions. Once you have the three above in your reading habit, the others slot in naturally.

---

## Pick your starting point

Quickstarts for the four roles we built this for. Each takes 5–10 minutes.

* [**For executives**](/insights-expo/expo-ai-adoption-for-executives) — the four numbers that tell the story; how to read a monthly summary in 60 seconds.
* [**For engineering leaders**](/insights-expo/expo-ai-adoption-for-engineering-leaders) — running a quarterly review using AI Tier, Output Score, and Cycle Time together.
* [**For team leads**](/insights-expo/expo-ai-adoption-for-team-leads) — your weekly pulse using the Flow + Team + Review tabs on /flow-delivery.
* [**For admins**](/insights-expo/expo-ai-adoption-for-admins) — settings, roster, integrations, and how to keep the data clean.

Or jump straight into product:

* [**Reading your first dashboard**](/insights-expo/expo-ai-adoption-first-dashboard) — a guided walkthrough of /teams.

---

## How to think about developer scores

A short philosophy note, because scores invite misuse.

Every developer in GitKraken Insights gets sorted into one of five tiers:

| Tier | Composite score | What it means |
| --- | --- | --- |
| **Power User** | ≥ 80 | Using AI consistently and shipping at the org's top rate |
| **Regular** | 55–79 | Healthy adoption and steady output |
| **Explorer** | 25–54 | Trying AI but not yet routine |
| **Emerging** | < 25 | Minimal AI engagement |
| **On PTO** | (override) | Was on PTO for every weekday in the window — excluded from averages |

The tiers describe **patterns of engagement and delivery**, not "who's a good engineer." A senior developer working on a hard, isolated infrastructure project for a quarter may show as Emerging _because the work doesn't suit AI tooling_. A junior developer pairing constantly with Cursor on UI changes may show as a Power User. Neither tells you who deserves a promotion.

What the tiers _do_ tell you:

* **Adoption trajectory.** How many developers crossed from Emerging → Explorer last quarter?
* **Cohort gaps.** Are your Power Users concentrated on one team while another team is stuck at Explorer?
* **Onboarding signal.** New hires landing in Emerging after 6 weeks is a process flag, not a person flag.
* **Org-level maturity.** What % of your team is at Regular or above? That's your AI rollout report card.

When in doubt: **don't read a single developer's score in isolation. Read the cohort.**

---

## A note on benchmarks

You'll see ranges throughout the help center labeled "Strong / Fair / Needs attention." These reflect our defaults and what we see across the orgs running Insights. They are not industry benchmarks. Engineering productivity has no good universal benchmarks — every team's context matters too much — so treat the ranges as a starting frame, not a verdict.

---

## Next steps

* New here? → [Reading your first dashboard](/insights-expo/expo-ai-adoption-first-dashboard)
* Have a specific question already? → [AI Adoption home](/insights-expo/expo-ai-adoption-home)
* Need to set things up? → [For admins](/insights-expo/expo-ai-adoption-for-admins) and the [Settings reference](/insights-expo/expo-ai-adoption-settings)
