---
title: Getting Started with GitKraken Insights (V1 CSS-Only)
description: Visual exploration V1 — CSS-only enhancement of the Getting Started page.
product: GitKraken Insights
content_type: install
audience: admin
plan_required: GitKraken Insights
integrations: [GitHub, GitLab, Bitbucket, Azure DevOps, Jira Cloud, Claude Code, Cursor, GitHub Copilot]
status: draft
taxonomy:
    category: insights-expo
---


<div class="breadcrumb-nav"><a href="/gk-insights/">GitKraken Insights</a><span class="sep">/</span><span class="current">Getting Started</span></div>

# Getting Started with GitKraken Insights

<kbd>Last updated: March 2026</kbd>

GitKraken Insights turns raw Git data into clear, useful metrics for developers and leaders. GitKraken Insights pulls code activity, pull requests, issues, and CI/CD results into a single view that fits directly into existing workflows.

Instead of surface-level stats, GitKraken Insights shows how work connects to team goals and points out ways to improve flow and productivity.

<div class="related-pages">
<h2>Explore GitKraken Insights</h2>
<div class="related-grid">
  <a href="/gk-insights/gk-insights-dora-metrics/" class="related-card">
    <span class="card-title">DORA Metrics</span>
    <span class="card-desc">Deploy frequency, change lead time, mean time to recover, and defect rate.</span>
  </a>
  <a href="/gk-insights/gk-insights-pr-metrics/" class="related-card">
    <span class="card-title">Pull Request Metrics</span>
    <span class="card-desc">Cycle time, review count, open time, PR size, and code review hours.</span>
  </a>
  <a href="/gk-insights/gk-insights-ai-impact-metrics/" class="related-card">
    <span class="card-title">AI Impact Metrics</span>
    <span class="card-desc">Prompt acceptance rate, duplicated code, code rework, and active users.</span>
  </a>
  <a href="/gk-insights/gk-insights-code-quality-metrics/" class="related-card">
    <span class="card-title">Code Quality Metrics</span>
    <span class="card-desc">Bug work percent, documentation and test percent, and code change rate.</span>
  </a>
  <a href="/gk-insights/gk-insights-velocity-metrics/" class="related-card">
    <span class="card-title">Velocity Metrics</span>
    <span class="card-desc">Commit count and estimated coding hours.</span>
  </a>
  <a href="/gk-insights/gk-insights-dashboard-management/" class="related-card">
    <span class="card-title">Dashboard Management</span>
    <span class="card-desc">Configure filters, group views, and chart display options.</span>
  </a>
  <a href="/gk-insights/gk-insights-metric-settings/" class="related-card">
    <span class="card-title">Metric Settings</span>
    <span class="card-desc">Adjust thresholds, targets, and metric calculation parameters.</span>
  </a>
  <a href="/gk-insights/gk-insights-faq/" class="related-card">
    <span class="card-title">FAQ</span>
    <span class="card-desc">Common questions about data, accuracy, access, and configuration.</span>
  </a>
</div>
</div>

> **Plan:** GitKraken Insights (available by request)
> **Platform:** Browser only via [gitkraken.dev](https://gitkraken.dev)
> **Role:** Lead, Admin, or Owner (for access). The **User** role does not grant access to GitKraken Insights but tells Insights to begin tracking that user for metrics.
> **Integrations:** GitHub, GitLab, Bitbucket, Azure DevOps, Jira Cloud
> **AI providers:** Claude Code, Cursor, GitHub Copilot (optional)

<figure>
  <img src="/wp-content/uploads/insights-dashboard-oct-2025.png" srcset="/wp-content/uploads/insights-dashboard-oct-2025@2x.png" class="help-center-img img-bordered" alt="Dashboard view of GitKraken Insights metrics and charts for development activity" />
  <figcaption style="text-align: center; color: #888">Overview of GitKraken Insights</figcaption>
</figure>

---

## Request Access

GitKraken Insights is available by request only. To get started, [request a guided tour](https://www.gitkraken.com/insights#form).  

 A member of the GitKraken team will contact you right away to walk you through GitKraken Insights and explain how to enable access for your organization.

**Note:** GitKraken Insights is a standalone product. [Contact GitKraken](https://www.gitkraken.com/insights#form) to get started.

---

## Connecting your data

Once your access is approved, you can connect GitKraken Insights to your repositories and configure settings for your organization.  

Currently, Insights supports connections with GitHub, GitLab, Bitbucket, Azure DevOps, and Jira Cloud.

In addition, you can connect AI providers to enable AI Impact insights (like Duplicated Code, Prompt Acceptance Rate, and more).

---

### 1. Import your repositories

1. In GitKraken.dev, go to **Insights > Data Connection**.  
2. Click to connect with GitHub, GitLab, Azure DevOps, Claude Code, Cursor, GitHub Copilot, Bitbucket, or Jira Cloud. 
3. Authorize GitKraken Insights to connect with GitHub.  
4. Select which repositories to track. Use the filter option at the top of the page to quickly narrow down the list.  

<figure>
  <img src="/wp-content/uploads/data-connection-dec-2025.png" srcset="/wp-content/uploads/data-connection-dec-2025@2x.png" class="help-center-img img-bordered" alt="Screenshot of Data Connection page to connect GitHub, GitLab, or Jira for Insights" />
  <figcaption style="text-align: center; color: #888">Connect GitHub, GitLab, or Jira to enable Insights</figcaption>
</figure>

<figure>
  <img src="/wp-content/uploads/authorize-gitclear.png" srcset="/wp-content/uploads/authorize-gitclear@2x.png" class="help-center-img img-bordered" alt="Screenshot authorizing GitHub access for GitKraken Insights" />
  <figcaption style="text-align: center; color: #888">Authorize GitHub access for GitKraken Insights</figcaption>
</figure>

<!-- FLAG FOR HUMAN REVIEW: import-repos.png is not present in the _images/ directory. Verify the correct filename. -->
<figure>
  <img src="/wp-content/uploads/import-repos.png" srcset="/wp-content/uploads/import-repos@2x.png" class="help-center-img img-bordered" alt="Screenshot of repository selection to choose which repositories to import into Insights" />
  <figcaption style="text-align: center; color: #888">Select which repos to import. You can always import more later.</figcaption>
</figure>

#### Avoiding GitHub API rate limits

If you're importing a large number of repositories, depending on size and commit history, you may encounter GitHub's hourly API rate limits. This can temporarily throttle other GitHub services used by your organization.

To avoid this, additional members of your organization can connect to GitKraken Insights using a [Lead role](/gk-dev/gk-dev-organization/#roles). When multiple people are connected, the app distributes processing across their GitHub tokens to help avoid throttling.

After the initial import is complete, rate limit issues are unlikely to recur.

---

### Connect an AI provider (optional)

As of February 2026, GitKraken Insights only supports connections with Claude Code, Cursor and GitHub Copilot to enable AI insights.

To enable AI Impact insights, connect your preferred AI provider:
1. In GitKraken.dev, go to [**Insights > Data Connection**](https://gitkraken.dev/insights/data-connections).
2. Click to `Manage` with Claude Code, Cursor or Github Copilot.
3. In the new window, select the AI provider you wish to connect with and enter the provider Token.
4. Click **Connect AI Provider** to finish the connection.

<figure>
  <img src="/wp-content/uploads/gk-dev-ai-provider-connection@2x.png" class="help-center-img img-bordered" alt="Screenshot of AI provider connection" />
  <figcaption style="text-align: center; color: #888">Connect your AI provider to enable AI Impact insights</figcaption>
</figure>

### 2. Confirm your profile details

After connecting repositories, confirm your personal details:

- First and last name  
- Time zone  
- Job role  

<figure>
  <img src="/wp-content/uploads/set-role-oct-2025.png" srcset="/wp-content/uploads/set-role-oct-2025@2x.png" class="help-center-img img-bordered" alt="Screenshot of profile form to confirm name, time zone, and job role before continuing" />
  <figcaption style="text-align: center; color: #888">Confirm your details before continuing</figcaption>
</figure>

---

### 3. Monitor data import progress

Once setup is complete, GitKraken Insights will begin importing your repository data.  

- Expect **past month's activity** to appear within a few hours.  
- Full **year's activity** is usually ready within one to two days.  
- Track import progress anytime from the **Dashboard** tab.  

<figure>
  <img src="/wp-content/uploads/import-progress.png" srcset="/wp-content/uploads/import-progress@2x.png" class="help-center-img img-bordered" alt="Dashboard view showing import progress while Insights processes your repository data" />
  <figcaption style="text-align: center; color: #888">Monitor import progress while Insights processes your data</figcaption>
</figure>

---



---

<div class="exploration-notes">
<h2>Exploration Notes: Version 1 — CSS-Only Enhancement</h2>
<p><strong>Approach:</strong> Added a <code>&lt;style&gt;</code> block at the top of the Markdown file. All content remains in standard Markdown. CSS targets the HTML elements that WordPress generates from Markdown rendering.</p>
<p><strong>What this tests:</strong> Whether CSS-only changes inside the .md file, processed through Git It Write, can meaningfully improve the visual presentation without changing the content structure.</p>
<p><strong>Limitations:</strong> No control over the WordPress page template, header, footer, or sidebar. CSS can only style what's inside the post content area. Three-column layout, sticky TOC, and modal search are not possible with this approach. Breadcrumbs and card navigation require inline HTML additions.</p>
<p><strong>Maintenance:</strong> Low — CSS block at the top of each file, content stays as Markdown. Could extract shared CSS to a WordPress custom CSS area to avoid duplication across files.</p>
</div>
