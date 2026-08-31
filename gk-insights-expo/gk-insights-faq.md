---
title: GitKraken Insights FAQ
description: Answers to common questions about GitKraken Insights for developer leaders, including access, integrations, available metrics, and customization.
product: GitKraken Insights
content_type: faq
audience: all
plan_required: GitKraken Insights
status: GA
taxonomy:
    category: gk-insights
---
<kbd>Last updated: March 2026</kbd>

Answers to common questions about GitKraken Insights, an engineering metrics platform that turns Git activity, pull requests, issues, and CI/CD results into actionable metrics. GitKraken Insights is accessed via [gitkraken.dev](https://gitkraken.dev). For setup instructions, see [Getting Started with GitKraken Insights](/gk-insights/gk-insights).

> **Plan:** GitKraken Insights (available by request)
> **Platform:** Browser only via [gitkraken.dev](https://gitkraken.dev)
> **Role:** Lead, Admin, or Owner

---

## What is GitKraken Insights?

GitKraken Insights turns raw Git data into clear, actionable engineering metrics. It pulls code activity, pull requests, issues, and CI/CD results into a single dashboard, giving both developers and leaders a shared view of how work is progressing.

---

## Who can access GitKraken Insights?

GitKraken Insights is accessible to members with the **Lead**, **Admin**, or **Owner** role. Members assigned the **User** role do not have access to the GitKraken Insights UI, but GitKraken Insights begins tracking them for metrics as soon as the User role is assigned. Assign the User role to team members you want to measure without granting them direct access.

---

## What plan is required for GitKraken Insights?

GitKraken Insights is a standalone product, purchased separately from other GitKraken plans. [Contact GitKraken](https://www.gitkraken.com/insights#form) to get pricing for your organization.

---

## How do I get access?

GitKraken Insights is available by request only. To get started, [request a guided tour](https://www.gitkraken.com/insights#form) and a member of the GitKraken team will walk you through Insights and explain how to enable access for your organization.

---

## What tools does GitKraken Insights connect to?

GitKraken Insights currently supports connections with **GitHub**, **GitLab** (cloud and self-hosted), **Bitbucket**, and **Jira Cloud**. Support for Azure DevOps is coming soon.

To enable AI Impact metrics, you can also connect **Claude Code**, **Cursor**, or **GitHub Copilot** as an AI provider.

---

## How does GitKraken Insights connect to a self-hosted GitLab instance?

Your self-hosted GitLab instance must be reachable from [gitkraken.dev](https://gitkraken.dev) over the public internet. GitKraken Insights authenticates with a **personal access token (PAT)** that you generate in GitLab and provide during setup.

---

## What metrics does GitKraken Insights track?

GitKraken Insights covers five metric categories:

| Category | Metrics | Page |
|---|---|---|
| DORA | Deploy Frequency, Change Lead Time, Mean Time to Repair/Recover, Defect Rate | [DORA Metrics](/gk-insights/gk-insights-dora-metrics) |
| Pull Requests | First Response Time, Cycle Time, Lead Time, Number of Reviews, Open Time, PRs Abandoned, PRs Merged Without Review, PR Comments, PR Size/Effort, Code Review Hours | [PR Metrics](/gk-insights/gk-insights-pr-metrics) |
| AI Impact | Copy/Paste vs Moved %, Duplicated Code, Percent of Code Rework, Post PR Work Occurring, Active Users, Suggestions, Prompt Acceptance Rate, Tab Acceptance Rate | [AI Impact Metrics](/gk-insights/gk-insights-ai-impact-metrics) |
| Code Quality | Bug Work Percent, Documentation and Test Percent, Code Change Rate, Code Change by Operation | [Code Quality Metrics](/gk-insights/gk-insights-code-quality-metrics) |
| Velocity | Commit Count, Estimated Coding Hours | [Velocity Metrics](/gk-insights/gk-insights-velocity-metrics) |

---

## Does GitKraken Insights work with AI coding tools?

Yes. GitKraken Insights connects to Claude Code, Cursor, and GitHub Copilot to surface AI Impact metrics. These metrics help teams measure how AI tools affect code quality and developer efficiency, including how often suggestions are accepted, how much code is duplicated or reworked, and how many developers are actively using AI tools.

---

## How long does it take to see data after connecting?

After setup is complete, GitKraken Insights begins importing your repository data automatically:

- **Past month's activity** is typically available within a few hours.
- **Full year's activity** is usually ready within one to two days.

You can track import progress at any time from the **Dashboard** tab.

---

## Do developers need to change their workflow?

No. GitKraken Insights reads from existing Git activity, pull requests, issues, and CI/CD data. Developers continue working in their normal tools. GitKraken Insights observes and measures without requiring any changes to how work gets done.

---

## Can I customize dashboards and the metrics they display?

Yes. You can create dashboards, add specific metrics as widgets, arrange the widget layout, and apply filters by Workspace, Repository, Timeframe, and Team. You can also add trendlines to individual metrics to visualize direction over time.

Note: each user can currently create one dashboard per organization. Support for multiple dashboards per user is planned for a future release.

---

## How does GitKraken Insights identify releases for DORA metrics?

You configure release detection rules in **Metric Settings > Release Tracking**. Rules can match on branch prefix, merged pull request target, git tags, commit messages, or API calls. GitKraken Insights attempts to apply each rule to all connected repositories automatically.

---

## How does GitKraken Insights identify critical defects?

Critical defects are identified two ways. In **Metric Settings > Critical Defect Terms**, you define strings that GitKraken Insights matches case-insensitively against branch names, linked issue titles, and pull request titles. In **Metric Settings > Define Issue Tracker Projects**, you configure the specific issue field values that mark a defect as critical within your issue tracker.
