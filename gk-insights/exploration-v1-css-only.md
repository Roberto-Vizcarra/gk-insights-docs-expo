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
    category: gk-insights
---

<style>
/* ==========================================================================
   V1 CSS-Only Enhancement - GitKraken Insights Help Center Exploration
   ========================================================================== 
   This style block targets standard HTML elements that WordPress generates
   from Markdown via the Git It Write plugin. All content below remains in
   Markdown; only presentation changes live here.
   ========================================================================== */
/* --- Custom Properties --------------------------------------------------- */
:root {
  /* Brand */
  --gk-green:        #00d084;
  --gk-green-light:  #e6faf1;
  --gk-green-dark:   #00a868;
  --gk-teal:         #0099cc;
  --gk-teal-light:   #e6f5fb;
  --gk-dark:         #1b2028;
  /* Neutral palette */
  --color-bg:        #ffffff;
  --color-surface:   #f8f9fb;
  --color-border:    #e2e5ea;
  --color-border-lt: #eef0f3;
  --color-text:      #2c3038;
  --color-text-sec:  #5f6672;
  --color-text-dim:  #888e99;
  /* Typography */
  --font-body:     'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  --font-mono:     'JetBrains Mono', 'Fira Code', 'SF Mono', 'Cascadia Code', Consolas, monospace;
  --font-size-sm:  0.875rem;
  --font-size-base: 1rem;
  --font-size-lg:  1.125rem;
  --line-height:   1.7;
  /* Layout */
  --content-width: 720px;
  --gap-xs:  0.5rem;
  --gap-sm:  1rem;
  --gap-md:  1.5rem;
  --gap-lg:  2.5rem;
  --gap-xl:  3.5rem;
  --radius:  8px;
  --radius-lg: 12px;
}
/* --- Base & Content Wrapper ----------------------------------------------- */
.entry-content,
.post-content,
.wp-block-post-content,
article .content {
  max-width: var(--content-width);
  margin-left: auto;
  margin-right: auto;
  font-family: var(--font-body);
  font-size: var(--font-size-base);
  line-height: var(--line-height);
  color: var(--color-text);
}
/* --- Typography ---------------------------------------------------------- */
h1 {
  font-size: 2.25rem;
  font-weight: 700;
  line-height: 1.2;
  letter-spacing: -0.025em;
  margin-top: 0;
  margin-bottom: var(--gap-sm);
  color: var(--gk-dark);
}
h2 {
  font-size: 1.5rem;
  font-weight: 650;
  line-height: 1.3;
  letter-spacing: -0.015em;
  margin-top: var(--gap-xl);
  margin-bottom: var(--gap-md);
  padding-bottom: 0.5rem;
  border-bottom: 2px solid var(--gk-green);
  color: var(--gk-dark);
}
h3 {
  font-size: 1.2rem;
  font-weight: 600;
  line-height: 1.4;
  margin-top: var(--gap-lg);
  margin-bottom: var(--gap-sm);
  color: var(--color-text);
}
h4 {
  font-size: 1.05rem;
  font-weight: 600;
  line-height: 1.4;
  margin-top: var(--gap-md);
  margin-bottom: var(--gap-xs);
  color: var(--color-text-sec);
}
p {
  margin-bottom: var(--gap-md);
}
a {
  color: var(--gk-teal);
  text-decoration: none;
  border-bottom: 1px solid transparent;
  transition: border-color 0.2s ease, color 0.2s ease;
}
a:hover {
  color: var(--gk-green-dark);
  border-bottom-color: var(--gk-green-dark);
}
strong {
  font-weight: 600;
  color: var(--gk-dark);
}
/* --- Blockquote as Callout Box ------------------------------------------- */
blockquote {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-left: 4px solid var(--gk-green);
  border-radius: var(--radius);
  padding: var(--gap-md) var(--gap-md) var(--gap-sm) var(--gap-md);
  margin: var(--gap-lg) 0;
  font-size: var(--font-size-sm);
  line-height: 1.8;
  color: var(--color-text-sec);
}
blockquote p {
  margin-bottom: var(--gap-xs);
}
blockquote strong {
  color: var(--gk-dark);
  font-weight: 600;
}
/* --- Lists --------------------------------------------------------------- */
ul, ol {
  margin-bottom: var(--gap-md);
  padding-left: 1.5rem;
}
li {
  margin-bottom: 0.4rem;
  line-height: var(--line-height);
}
li + li {
  margin-top: 0.25rem;
}
ol li {
  padding-left: 0.25rem;
}
ol li::marker {
  font-weight: 600;
  color: var(--gk-green-dark);
}
/* --- Images & Figures ---------------------------------------------------- */
figure {
  margin: var(--gap-lg) 0;
  padding: 0;
  text-align: center;
}
figure img,
.help-center-img {
  max-width: 100%;
  height: auto;
  border-radius: var(--radius-lg);
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08), 0 0 0 1px var(--color-border-lt);
  transition: box-shadow 0.25s ease;
}
figure img:hover,
.help-center-img:hover {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12), 0 0 0 1px var(--color-border);
}
figure img.img-bordered {
  border: 1px solid var(--color-border);
}
figcaption {
  font-size: var(--font-size-sm);
  color: var(--color-text-dim) !important;
  margin-top: var(--gap-sm);
  font-style: italic;
}
/* --- Horizontal Rules ---------------------------------------------------- */
hr {
  border: none;
  height: 1px;
  background: linear-gradient(
    to right,
    transparent,
    var(--color-border) 15%,
    var(--color-border) 85%,
    transparent
  );
  margin: var(--gap-xl) 0;
}
/* --- Code & Kbd ---------------------------------------------------------- */
code {
  font-family: var(--font-mono);
  font-size: 0.88em;
  background: var(--gk-teal-light);
  color: var(--gk-dark);
  padding: 0.15em 0.4em;
  border-radius: 4px;
  border: 1px solid var(--color-border-lt);
}
kbd {
  font-family: var(--font-body);
  font-size: var(--font-size-sm);
  color: var(--color-text-dim);
  background: none;
  border: none;
  padding: 0;
  display: inline-block;
  margin-bottom: var(--gap-sm);
}
/* --- Tables -------------------------------------------------------------- */
table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  margin: var(--gap-lg) 0;
  border: 1px solid var(--color-border);
  border-radius: var(--radius);
  overflow: hidden;
  font-size: var(--font-size-sm);
}
thead {
  background: var(--color-surface);
}
th {
  font-weight: 600;
  color: var(--gk-dark);
  text-align: left;
  padding: 0.75rem 1rem;
  border-bottom: 2px solid var(--color-border);
}
td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--color-border-lt);
  color: var(--color-text);
}
tr:last-child td {
  border-bottom: none;
}
tr:hover td {
  background: var(--gk-green-light);
}
/* --- Breadcrumb Navigation ----------------------------------------------- */
.breadcrumb-nav {
  font-size: var(--font-size-sm);
  color: var(--color-text-dim);
  margin-bottom: var(--gap-lg);
  padding: var(--gap-xs) 0;
  font-family: var(--font-body);
}
.breadcrumb-nav a {
  color: var(--color-text-sec);
  text-decoration: none;
  border-bottom: none;
}
.breadcrumb-nav a:hover {
  color: var(--gk-teal);
}
.breadcrumb-nav .sep {
  margin: 0 0.4rem;
  color: var(--color-border);
}
.breadcrumb-nav .current {
  color: var(--color-text);
  font-weight: 500;
}
/* --- Related Pages Card Grid --------------------------------------------- */
.related-pages {
  margin-top: var(--gap-xl);
  padding-top: var(--gap-lg);
  border-top: 2px solid var(--gk-green);
}
.related-pages h2 {
  border-bottom: none;
  margin-top: 0;
  padding-bottom: 0;
  font-size: 1.35rem;
}
.related-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: var(--gap-sm);
  margin-top: var(--gap-md);
}
.related-card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius);
  padding: var(--gap-md);
  text-decoration: none;
  color: var(--color-text);
  transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.15s ease;
  display: block;
}
.related-card:hover {
  border-color: var(--gk-green);
  box-shadow: 0 2px 8px rgba(0, 208, 132, 0.12);
  transform: translateY(-1px);
}
.related-card .card-title {
  font-weight: 600;
  font-size: var(--font-size-base);
  color: var(--gk-dark);
  margin-bottom: 0.35rem;
  display: block;
}
.related-card .card-desc {
  font-size: var(--font-size-sm);
  color: var(--color-text-sec);
  line-height: 1.5;
  display: block;
}
/* --- Exploration Notes --------------------------------------------------- */
.exploration-notes {
  margin-top: var(--gap-xl);
  padding: var(--gap-lg);
  background: #fefbf0;
  border: 1px dashed #d4a843;
  border-radius: var(--radius-lg);
  font-size: var(--font-size-sm);
  line-height: 1.7;
  color: var(--color-text-sec);
}
.exploration-notes h2 {
  font-size: 1.15rem;
  color: #8a6d20;
  border-bottom: 1px solid #e8d89c;
  margin-top: 0;
  padding-bottom: 0.4rem;
  margin-bottom: var(--gap-sm);
}
.exploration-notes p {
  margin-bottom: var(--gap-sm);
}
.exploration-notes p:last-child {
  margin-bottom: 0;
}
.exploration-notes code {
  background: #fef6dc;
  border-color: #e8d89c;
  font-size: 0.85em;
}
</style>

<div class="breadcrumb-nav">
  <a href="/gk-insights/">GitKraken Insights</a>
  <span class="sep">/</span>
  <span class="current">Getting Started</span>
</div>

# Getting Started with GitKraken Insights

<kbd>Last updated: March 2026</kbd>

GitKraken Insights turns raw Git data into clear, useful metrics for developers and leaders. GitKraken Insights pulls code activity, pull requests, issues, and CI/CD results into a single view that fits directly into existing workflows.

Instead of surface-level stats, GitKraken Insights shows how work connects to team goals and points out ways to improve flow and productivity.

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

---

<div class="exploration-notes">
<h2>Exploration Notes: Version 1 — CSS-Only Enhancement</h2>
<p><strong>Approach:</strong> Added a <code>&lt;style&gt;</code> block at the top of the Markdown file. All content remains in standard Markdown. CSS targets the HTML elements that WordPress generates from Markdown rendering.</p>
<p><strong>What this tests:</strong> Whether CSS-only changes inside the .md file, processed through Git It Write, can meaningfully improve the visual presentation without changing the content structure.</p>
<p><strong>Limitations:</strong> No control over the WordPress page template, header, footer, or sidebar. CSS can only style what's inside the post content area. Three-column layout, sticky TOC, and modal search are not possible with this approach. Breadcrumbs and card navigation require inline HTML additions.</p>
<p><strong>Maintenance:</strong> Low — CSS block at the top of each file, content stays as Markdown. Could extract shared CSS to a WordPress custom CSS area to avoid duplication across files.</p>
</div>
