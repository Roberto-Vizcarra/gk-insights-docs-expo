---
title: Getting Started with GitKraken Insights (V2 HTML/CSS Cards)
description: Visual exploration V2 — full HTML/CSS card layout with breadcrumbs.
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
/* ============================================================
   GitKraken Insights — V2 Full HTML/CSS Card Layout
   ============================================================ */

:root {
  --gki-accent: #0D9488;
  --gki-accent-hover: #0F766E;
  --gki-accent-light: #CCFBF1;
  --gki-accent-bg: #F0FDFA;
  --gki-text: #1C1917;
  --gki-text-secondary: #57534E;
  --gki-text-muted: #A8A29E;
  --gki-border: #E7E5E4;
  --gki-border-strong: #D6D3D1;
  --gki-bg: #FFFFFF;
  --gki-bg-subtle: #FAFAF9;
  --gki-bg-offset: #F5F5F4;
  --gki-info-bg: #EFF6FF;
  --gki-info-border: #BFDBFE;
  --gki-info-text: #1E40AF;
  --gki-info-icon: #3B82F6;
  --gki-warn-bg: #FFFBEB;
  --gki-warn-border: #FDE68A;
  --gki-warn-text: #92400E;
  --gki-warn-icon: #F59E0B;
  --gki-tip-bg: #F0FDFA;
  --gki-tip-border: #99F6E4;
  --gki-tip-text: #134E4A;
  --gki-tip-icon: #14B8A6;
  --gki-radius: 8px;
  --gki-radius-lg: 12px;
  --gki-shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
  --gki-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
  --gki-shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
  --gki-font: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  --gki-font-mono: "SF Mono", "Cascadia Code", "Fira Code", Consolas, monospace;
}

/* ---------- Reset scope ---------- */
.gki-page * {
  box-sizing: border-box;
}

.gki-page {
  font-family: var(--gki-font);
  color: var(--gki-text);
  line-height: 1.7;
  max-width: 860px;
  margin: 0 auto;
}

/* ---------- Breadcrumb ---------- */
.gki-breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.85rem;
  color: var(--gki-text-muted);
  margin-bottom: 1.75rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--gki-border);
}

.gki-breadcrumb a {
  color: var(--gki-accent);
  text-decoration: none;
  font-weight: 500;
}

.gki-breadcrumb a:hover {
  text-decoration: underline;
}

.gki-breadcrumb .gki-crumb-sep {
  color: var(--gki-border-strong);
  user-select: none;
}

.gki-breadcrumb .gki-crumb-current {
  color: var(--gki-text-secondary);
  font-weight: 500;
}

/* ---------- Page title ---------- */
.gki-page-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--gki-text);
  margin: 0 0 0.5rem 0;
  line-height: 1.25;
  letter-spacing: -0.01em;
}

.gki-page-updated {
  display: inline-block;
  font-size: 0.8rem;
  color: var(--gki-text-muted);
  background: var(--gki-bg-offset);
  padding: 0.2rem 0.6rem;
  border-radius: 4px;
  margin-bottom: 1.5rem;
}

/* ---------- Intro text ---------- */
.gki-intro {
  font-size: 1.08rem;
  color: var(--gki-text-secondary);
  line-height: 1.75;
  margin-bottom: 2rem;
}

/* ---------- Requirements card ---------- */
.gki-requirements {
  background: var(--gki-bg-subtle);
  border: 1px solid var(--gki-border);
  border-left: 4px solid var(--gki-accent);
  border-radius: var(--gki-radius);
  padding: 1.25rem 1.5rem;
  margin-bottom: 2.5rem;
}

.gki-requirements-title {
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--gki-accent);
  margin: 0 0 0.75rem 0;
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.gki-requirements-title svg {
  flex-shrink: 0;
}

.gki-req-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem 2rem;
}

@media (max-width: 600px) {
  .gki-req-grid {
    grid-template-columns: 1fr;
  }
}

.gki-req-item {
  display: flex;
  align-items: baseline;
  gap: 0.4rem;
  font-size: 0.93rem;
  line-height: 1.55;
  padding: 0.25rem 0;
}

.gki-req-label {
  font-weight: 600;
  color: var(--gki-text);
  white-space: nowrap;
}

.gki-req-value {
  color: var(--gki-text-secondary);
}

.gki-req-value a {
  color: var(--gki-accent);
  text-decoration: none;
}

.gki-req-value a:hover {
  text-decoration: underline;
}

/* ---------- Hero figure ---------- */
.gki-hero-figure {
  margin: 0 0 2.5rem 0;
  padding: 0;
}

.gki-hero-figure img {
  display: block;
  width: 100%;
  height: auto;
  border: 1px solid var(--gki-border);
  border-radius: var(--gki-radius-lg);
  box-shadow: var(--gki-shadow-md);
}

.gki-hero-figure figcaption {
  text-align: center;
  font-size: 0.85rem;
  color: var(--gki-text-muted);
  margin-top: 0.6rem;
}

/* ---------- Section ---------- */
.gki-section {
  margin-bottom: 2.5rem;
  padding-bottom: 2.5rem;
  border-bottom: 1px solid var(--gki-border);
}

.gki-section:last-of-type {
  border-bottom: none;
}

.gki-section-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--gki-text);
  margin: 0 0 1rem 0;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid var(--gki-accent);
  display: inline-block;
}

.gki-subsection-title {
  font-size: 1.2rem;
  font-weight: 600;
  color: var(--gki-text);
  margin: 2rem 0 0.75rem 0;
}

/* ---------- Callout boxes ---------- */
.gki-callout {
  border-radius: var(--gki-radius);
  padding: 1rem 1.25rem;
  margin: 1.25rem 0;
  font-size: 0.93rem;
  line-height: 1.6;
  display: flex;
  gap: 0.75rem;
  align-items: flex-start;
}

.gki-callout p {
  margin: 0;
}

.gki-callout p + p {
  margin-top: 0.5rem;
}

.gki-callout-icon {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
  margin-top: 0.15rem;
}

.gki-callout--tip {
  background: var(--gki-tip-bg);
  border: 1px solid var(--gki-tip-border);
  color: var(--gki-tip-text);
}

.gki-callout--info {
  background: var(--gki-info-bg);
  border: 1px solid var(--gki-info-border);
  color: var(--gki-info-text);
}

.gki-callout--warn {
  background: var(--gki-warn-bg);
  border: 1px solid var(--gki-warn-border);
  color: var(--gki-warn-text);
}

.gki-callout a {
  font-weight: 600;
  text-decoration: underline;
}

.gki-callout--tip a  { color: var(--gki-accent-hover); }
.gki-callout--info a { color: var(--gki-info-text); }
.gki-callout--warn a { color: var(--gki-warn-text); }

/* ---------- Step flow ---------- */
.gki-steps {
  counter-reset: gki-step;
  list-style: none;
  padding: 0;
  margin: 1.25rem 0;
}

.gki-step {
  counter-increment: gki-step;
  display: flex;
  gap: 1rem;
  padding: 0.75rem 0;
  align-items: flex-start;
}

.gki-step + .gki-step {
  border-top: 1px dashed var(--gki-border);
}

.gki-step::before {
  content: counter(gki-step);
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: var(--gki-accent);
  color: #fff;
  font-size: 0.85rem;
  font-weight: 700;
  border-radius: 50%;
  margin-top: 0.1rem;
}

.gki-step-content {
  flex: 1;
  font-size: 0.95rem;
  line-height: 1.65;
}

.gki-step-content strong {
  color: var(--gki-text);
}

.gki-step-content code {
  background: var(--gki-bg-offset);
  padding: 0.15rem 0.4rem;
  border-radius: 4px;
  font-family: var(--gki-font-mono);
  font-size: 0.88em;
  color: var(--gki-accent-hover);
}

/* ---------- Screenshot figures ---------- */
.gki-figure {
  margin: 1.5rem 0;
  padding: 0;
}

.gki-figure img {
  display: block;
  width: 100%;
  max-width: 100%;
  height: auto;
  border: 1px solid var(--gki-border);
  border-radius: var(--gki-radius);
  box-shadow: var(--gki-shadow-sm);
}

.gki-figure figcaption {
  text-align: center;
  font-size: 0.83rem;
  color: var(--gki-text-muted);
  margin-top: 0.5rem;
  font-style: italic;
}

/* ---------- Inline list ---------- */
.gki-list {
  margin: 0.75rem 0;
  padding-left: 1.25rem;
  line-height: 1.75;
}

.gki-list li {
  margin-bottom: 0.3rem;
  font-size: 0.95rem;
}

.gki-list li strong {
  color: var(--gki-text);
}

/* ---------- Related pages card grid ---------- */
.gki-related {
  margin-top: 3rem;
}

.gki-related-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--gki-text);
  margin: 0 0 1.25rem 0;
}

.gki-card-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

@media (max-width: 768px) {
  .gki-card-grid {
    grid-template-columns: 1fr;
  }
}

@media (min-width: 769px) and (max-width: 960px) {
  .gki-card-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.gki-card {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  background: var(--gki-bg);
  border: 1px solid var(--gki-border);
  border-radius: var(--gki-radius);
  padding: 1.15rem 1.25rem;
  text-decoration: none;
  color: var(--gki-text);
  transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
  box-shadow: var(--gki-shadow-sm);
}

.gki-card:hover {
  border-color: var(--gki-accent);
  box-shadow: var(--gki-shadow-md);
  transform: translateY(-2px);
}

.gki-card-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--gki-text);
  margin: 0 0 0.3rem 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.gki-card-arrow {
  flex-shrink: 0;
  color: var(--gki-text-muted);
  transition: color 0.15s ease, transform 0.15s ease;
}

.gki-card:hover .gki-card-arrow {
  color: var(--gki-accent);
  transform: translateX(2px);
}

.gki-card-desc {
  font-size: 0.83rem;
  color: var(--gki-text-secondary);
  line-height: 1.5;
  margin: 0;
}
</style>

<div class="gki-page">

<!-- Breadcrumb navigation -->
<nav class="gki-breadcrumb" aria-label="Breadcrumb">
  <a href="/gk-insights/">GitKraken Insights</a>
  <span class="gki-crumb-sep" aria-hidden="true">/</span>
  <span class="gki-crumb-current" aria-current="page">Getting Started</span>
</nav>

<!-- Page title -->
<h1 class="gki-page-title">Getting Started with GitKraken Insights</h1>
<span class="gki-page-updated">Last updated: March 2026</span>

<!-- Intro -->
<p class="gki-intro">
  GitKraken Insights turns raw Git data into clear, useful metrics for developers and leaders. It pulls code activity, pull requests, issues, and CI/CD results into a single view that fits directly into existing workflows. Instead of surface-level stats, GitKraken Insights shows how work connects to team goals and points out ways to improve flow and productivity.
</p>

<!-- Requirements card -->
<div class="gki-requirements">
  <div class="gki-requirements-title">
    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 1.5a6.5 6.5 0 100 13 6.5 6.5 0 000-13zM7.25 5a.75.75 0 111.5 0 .75.75 0 01-1.5 0zM7 7h2v4.5H7V7z" fill="currentColor"/></svg>
    Requirements
  </div>
  <div class="gki-req-grid">
    <div class="gki-req-item">
      <span class="gki-req-label">Plan:</span>
      <span class="gki-req-value">GitKraken Insights (available by request)</span>
    </div>
    <div class="gki-req-item">
      <span class="gki-req-label">Platform:</span>
      <span class="gki-req-value">Browser only via <a href="https://gitkraken.dev">gitkraken.dev</a></span>
    </div>
    <div class="gki-req-item">
      <span class="gki-req-label">Role:</span>
      <span class="gki-req-value">Lead, Admin, or Owner</span>
    </div>
    <div class="gki-req-item">
      <span class="gki-req-label">Integrations:</span>
      <span class="gki-req-value">GitHub, GitLab, Bitbucket, Azure DevOps, Jira Cloud</span>
    </div>
    <div class="gki-req-item" style="grid-column: 1 / -1;">
      <span class="gki-req-label">AI providers:</span>
      <span class="gki-req-value">Claude Code, Cursor, GitHub Copilot (optional)</span>
    </div>
  </div>
</div>

<!-- Hero screenshot -->
<figure class="gki-hero-figure">
  <img src="/wp-content/uploads/insights-dashboard-oct-2025.png" srcset="/wp-content/uploads/insights-dashboard-oct-2025@2x.png" class="help-center-img img-bordered" alt="Dashboard view of GitKraken Insights metrics and charts for development activity" />
  <figcaption>Overview of GitKraken Insights</figcaption>
</figure>

<!-- ============================================================
     Request Access
     ============================================================ -->
<section class="gki-section">
  <h2 class="gki-section-title">Request Access</h2>

  <p>
    GitKraken Insights is available by request only. To get started,
    <a href="https://www.gitkraken.com/insights#form" style="color:var(--gki-accent);font-weight:600;">request a guided tour</a>.
    A member of the GitKraken team will contact you right away to walk you through GitKraken Insights and explain how to enable access for your organization.
  </p>

  <div class="gki-callout gki-callout--tip">
    <svg class="gki-callout-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 10-2 0 1 1 0 002 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
    <div>
      <p><strong>Note:</strong> GitKraken Insights is a standalone product. <a href="https://www.gitkraken.com/insights#form">Contact GitKraken</a> to get started.</p>
    </div>
  </div>
</section>

<!-- ============================================================
     Connecting Your Data
     ============================================================ -->
<section class="gki-section">
  <h2 class="gki-section-title">Connecting Your Data</h2>

  <p>
    Once your access is approved, you can connect GitKraken Insights to your repositories and configure settings for your organization. Currently, Insights supports connections with GitHub, GitLab, Bitbucket, Azure DevOps, and Jira Cloud.
  </p>
  <p>
    In addition, you can connect AI providers to enable AI Impact insights (like Duplicated Code, Prompt Acceptance Rate, and more).
  </p>

  <!-- ---- Import your repositories ---- -->
  <h3 class="gki-subsection-title">Import Your Repositories</h3>

  <ol class="gki-steps">
    <li class="gki-step">
      <div class="gki-step-content">In GitKraken.dev, go to <strong>Insights &gt; Data Connection</strong>.</div>
    </li>
    <li class="gki-step">
      <div class="gki-step-content">Click to connect with GitHub, GitLab, Azure DevOps, Claude Code, Cursor, GitHub Copilot, Bitbucket, or Jira Cloud.</div>
    </li>
    <li class="gki-step">
      <div class="gki-step-content">Authorize GitKraken Insights to connect with GitHub.</div>
    </li>
    <li class="gki-step">
      <div class="gki-step-content">Select which repositories to track. Use the filter option at the top of the page to quickly narrow down the list.</div>
    </li>
  </ol>

  <figure class="gki-figure">
    <img src="/wp-content/uploads/data-connection-dec-2025.png" srcset="/wp-content/uploads/data-connection-dec-2025@2x.png" class="help-center-img img-bordered" alt="Screenshot of Data Connection page to connect GitHub, GitLab, or Jira for Insights" />
    <figcaption>Connect GitHub, GitLab, or Jira to enable Insights</figcaption>
  </figure>

  <figure class="gki-figure">
    <img src="/wp-content/uploads/authorize-gitclear.png" srcset="/wp-content/uploads/authorize-gitclear@2x.png" class="help-center-img img-bordered" alt="Screenshot authorizing GitHub access for GitKraken Insights" />
    <figcaption>Authorize GitHub access for GitKraken Insights</figcaption>
  </figure>

  <!-- FLAG FOR HUMAN REVIEW: import-repos.png is not present in the _images/ directory. Verify the correct filename. -->
  <figure class="gki-figure">
    <img src="/wp-content/uploads/import-repos.png" srcset="/wp-content/uploads/import-repos@2x.png" class="help-center-img img-bordered" alt="Screenshot of repository selection to choose which repositories to import into Insights" />
    <figcaption>Select which repos to import. You can always import more later.</figcaption>
  </figure>

  <!-- ---- API rate limits ---- -->
  <h4 class="gki-subsection-title" style="font-size:1.05rem;">Avoiding GitHub API Rate Limits</h4>

  <div class="gki-callout gki-callout--warn">
    <svg class="gki-callout-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
    <div>
      <p>If you're importing a large number of repositories, depending on size and commit history, you may encounter GitHub's hourly API rate limits. This can temporarily throttle other GitHub services used by your organization.</p>
      <p>To avoid this, additional members of your organization can connect to GitKraken Insights using a <a href="/gk-dev/gk-dev-organization/#roles">Lead role</a>. When multiple people are connected, the app distributes processing across their GitHub tokens to help avoid throttling.</p>
      <p>After the initial import is complete, rate limit issues are unlikely to recur.</p>
    </div>
  </div>

  <!-- ---- Connect an AI provider ---- -->
  <h3 class="gki-subsection-title">Connect an AI Provider (Optional)</h3>

  <p>As of February 2026, GitKraken Insights only supports connections with Claude Code, Cursor and GitHub Copilot to enable AI insights.</p>

  <p>To enable AI Impact insights, connect your preferred AI provider:</p>

  <ol class="gki-steps">
    <li class="gki-step">
      <div class="gki-step-content">In GitKraken.dev, go to <a href="https://gitkraken.dev/insights/data-connections" style="color:var(--gki-accent);font-weight:600;">Insights &gt; Data Connection</a>.</div>
    </li>
    <li class="gki-step">
      <div class="gki-step-content">Click to <code>Manage</code> with Claude Code, Cursor or GitHub Copilot.</div>
    </li>
    <li class="gki-step">
      <div class="gki-step-content">In the new window, select the AI provider you wish to connect with and enter the provider Token.</div>
    </li>
    <li class="gki-step">
      <div class="gki-step-content">Click <strong>Connect AI Provider</strong> to finish the connection.</div>
    </li>
  </ol>

  <figure class="gki-figure">
    <img src="/wp-content/uploads/gk-dev-ai-provider-connection@2x.png" class="help-center-img img-bordered" alt="Screenshot of AI provider connection" />
    <figcaption>Connect your AI provider to enable AI Impact insights</figcaption>
  </figure>

  <!-- ---- Confirm your profile details ---- -->
  <h3 class="gki-subsection-title">Confirm Your Profile Details</h3>

  <p>After connecting repositories, confirm your personal details:</p>

  <ul class="gki-list">
    <li>First and last name</li>
    <li>Time zone</li>
    <li>Job role</li>
  </ul>

  <figure class="gki-figure">
    <img src="/wp-content/uploads/set-role-oct-2025.png" srcset="/wp-content/uploads/set-role-oct-2025@2x.png" class="help-center-img img-bordered" alt="Screenshot of profile form to confirm name, time zone, and job role before continuing" />
    <figcaption>Confirm your details before continuing</figcaption>
  </figure>

  <!-- ---- Monitor data import progress ---- -->
  <h3 class="gki-subsection-title">Monitor Data Import Progress</h3>

  <p>Once setup is complete, GitKraken Insights will begin importing your repository data.</p>

  <ul class="gki-list">
    <li><strong>Past month's activity</strong> appears within a few hours.</li>
    <li><strong>Full year's activity</strong> is usually ready within one to two days.</li>
    <li>Track import progress anytime from the <strong>Dashboard</strong> tab.</li>
  </ul>

  <figure class="gki-figure">
    <img src="/wp-content/uploads/import-progress.png" srcset="/wp-content/uploads/import-progress@2x.png" class="help-center-img img-bordered" alt="Dashboard view showing import progress while Insights processes your repository data" />
    <figcaption>Monitor import progress while Insights processes your data</figcaption>
  </figure>
</section>

<!-- ============================================================
     Related Pages
     ============================================================ -->
<section class="gki-related">
  <h2 class="gki-related-title">Explore GitKraken Insights</h2>

  <div class="gki-card-grid">

    <a href="/gk-insights/gk-insights-dora-metrics" class="gki-card">
      <div>
        <div class="gki-card-title">
          DORA Metrics
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Deploy Frequency, Change Lead Time, Mean Time to Repair, and Defect Rate.</p>
      </div>
    </a>

    <a href="/gk-insights/gk-insights-pr-metrics" class="gki-card">
      <div>
        <div class="gki-card-title">
          Pull Request Metrics
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Cycle Time, First Response Time, Open Time, PR Size, and review activity.</p>
      </div>
    </a>

    <a href="/gk-insights/gk-insights-ai-impact-metrics" class="gki-card">
      <div>
        <div class="gki-card-title">
          AI Impact Metrics
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Prompt Acceptance Rate, Duplicated Code, Code Rework, and AI usage.</p>
      </div>
    </a>

    <a href="/gk-insights/gk-insights-code-quality-metrics" class="gki-card">
      <div>
        <div class="gki-card-title">
          Code Quality Metrics
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Bug Work Percent, Documentation and Test Percent, and Code Change Rate.</p>
      </div>
    </a>

    <a href="/gk-insights/gk-insights-velocity-metrics" class="gki-card">
      <div>
        <div class="gki-card-title">
          Velocity Metrics
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Commit Count and Estimated Coding Hours for delivery consistency.</p>
      </div>
    </a>

    <a href="/gk-insights/gk-insights-dashboard-management" class="gki-card">
      <div>
        <div class="gki-card-title">
          Dashboard Management
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Configure filters, layouts, and dashboard views for your team.</p>
      </div>
    </a>

    <a href="/gk-insights/gk-insights-metric-settings" class="gki-card">
      <div>
        <div class="gki-card-title">
          Metric Settings
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Customize thresholds, date ranges, and metric calculation settings.</p>
      </div>
    </a>

    <a href="/gk-insights/gk-insights-faq" class="gki-card">
      <div>
        <div class="gki-card-title">
          FAQ
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Common questions about setup, data sources, metrics, and troubleshooting.</p>
      </div>
    </a>

  </div>
</section>

</div><!-- /.gki-page -->

<div class="exploration-notes" style="margin-top:3rem; padding:1.5rem; background:#f5f5f4; border:1px solid #e0ddd8; border-radius:8px;">
<h2 style="font-size:1.1rem; margin-top:0;">Exploration Notes: Version 2 — Full HTML/CSS Card Layout</h2>
<p><strong>Approach:</strong> Replaced Markdown content with semantic HTML and embedded CSS. Added breadcrumb navigation, card grid for related pages, styled info boxes, and refined visual hierarchy.</p>
<p><strong>What this tests:</strong> Whether fully-authored HTML inside .md files, processed through Git It Write, can deliver a modern card-based docs experience within WordPress. This is the ceiling of what the "better HTML/CSS in the repo" approach can achieve.</p>
<p><strong>Limitations:</strong> The WordPress page template still controls the header, footer, and sidebar — this only styles the post content area. Every page needs its own copy of the CSS (or it needs to be extracted to WP's custom CSS). No JavaScript interactivity (that's V3). The file is no longer readable as Markdown — it's effectively an HTML file with a .md extension.</p>
<p><strong>Maintenance:</strong> Medium-high. HTML content is harder to edit than Markdown. CSS is duplicated per file unless extracted. Layout changes require updating every file. A shared CSS approach (WordPress Customizer or a small plugin) would reduce duplication.</p>
</div>
