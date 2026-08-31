---
title: Getting Started with GitKraken Insights (V3 Interactive)
description: Visual exploration V3 — HTML/CSS/JS with sticky TOC, collapsible sections, scroll tracking, and search.
product: GitKraken Insights
content_type: install
audience: admin
plan_required: GitKraken Insights
integrations: [GitHub, GitLab, Bitbucket, Azure DevOps, Jira Cloud, Claude Code, Cursor, GitHub Copilot]
status: draft
taxonomy:
    category: insights-expo
---


<div class="gki-page">
<!-- Breadcrumb -->
<nav class="gki-breadcrumb" aria-label="Breadcrumb"><a href="/gk-insights-expo/">GitKraken Insights</a><span class="gki-crumb-sep" aria-hidden="true">/</span><span class="gki-crumb-current" aria-current="page">Getting Started</span></nav>
<!-- Page title -->
<h1 class="gki-page-title">Getting Started with GitKraken Insights</h1>
<span class="gki-page-updated">Last updated: March 2026</span>
<!-- Intro -->
<p class="gki-intro">
  GitKraken Insights turns raw Git data into clear, useful metrics for developers and leaders. It pulls code activity, pull requests, issues, and CI/CD results into a single view that fits directly into existing workflows. Instead of surface-level stats, GitKraken Insights shows how work connects to team goals and points out ways to improve flow and productivity.
</p>
<section class="gki-related" id="explore">
  <h2 class="gki-related-title">Explore GitKraken Insights</h2>
  <div class="gki-card-grid" id="gkiCardGrid">
    <a href="/gk-insights-expo/gk-insights-dora-metrics" class="gki-card" data-search="dora deploy frequency change lead time mean time repair defect rate">
      <div>
        <div class="gki-card-title">
          DORA Metrics
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Deploy Frequency, Change Lead Time, Mean Time to Repair, and Defect Rate.</p>
      </div>
    </a>
    <a href="/gk-insights-expo/gk-insights-pr-metrics" class="gki-card" data-search="pull request cycle time first response open time pr size review comments merged abandoned">
      <div>
        <div class="gki-card-title">
          Pull Request Metrics
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Cycle Time, First Response Time, Open Time, PR Size, and review activity.</p>
      </div>
    </a>
    <a href="/gk-insights-expo/gk-insights-ai-impact-metrics" class="gki-card" data-search="ai impact prompt acceptance tab duplicated code rework active users suggestions copilot cursor claude">
      <div>
        <div class="gki-card-title">
          AI Impact Metrics
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Prompt Acceptance Rate, Duplicated Code, Code Rework, and AI usage.</p>
      </div>
    </a>
    <a href="/gk-insights-expo/gk-insights-code-quality-metrics" class="gki-card" data-search="code quality bug work percent documentation test change rate operation">
      <div>
        <div class="gki-card-title">
          Code Quality Metrics
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Bug Work Percent, Documentation and Test Percent, and Code Change Rate.</p>
      </div>
    </a>
    <a href="/gk-insights-expo/gk-insights-velocity-metrics" class="gki-card" data-search="velocity delivery consistency commit count estimated coding hours">
      <div>
        <div class="gki-card-title">
          Velocity Metrics
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Commit Count and Estimated Coding Hours for delivery consistency.</p>
      </div>
    </a>
    <a href="/gk-insights-expo/gk-insights-dashboard-management" class="gki-card" data-search="dashboard configure filters layout views team management">
      <div>
        <div class="gki-card-title">
          Dashboard Management
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Configure filters, layouts, and dashboard views for your team.</p>
      </div>
    </a>
    <a href="/gk-insights-expo/gk-insights-metric-settings" class="gki-card" data-search="metric settings thresholds date ranges calculation release tracking">
      <div>
        <div class="gki-card-title">
          Metric Settings
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Customize thresholds, date ranges, and metric calculation settings.</p>
      </div>
    </a>
    <a href="/gk-insights-expo/gk-insights-faq" class="gki-card" data-search="faq frequently asked questions troubleshooting help setup data sources">
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
     Related Pages with Search
     ============================================================ -->
<!-- ============================================================
     Request Access
     ============================================================ -->
<section class="gki-section" id="request-access">
  <h2 class="gki-section-title">Request Access</h2>
  <p>
    GitKraken Insights is available by request only. To get started,
    <a href="https://www.gitkraken.com/insights#form">request a guided tour</a>.
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
<section class="gki-section" id="connecting-data">
  <h2 class="gki-section-title">Connecting Your Data</h2>
  <p>
    Once your access is approved, you can connect GitKraken Insights to your repositories and configure settings for your organization. Currently, Insights supports connections with GitHub, GitLab, Bitbucket, Azure DevOps, and Jira Cloud.
  </p>
  <p>
    In addition, you can connect AI providers to enable AI Impact insights (like Duplicated Code, Prompt Acceptance Rate, and more).
  </p>
  <!-- Import repos -->
  <h3 class="gki-subsection-title" id="import-repos">Import Your Repositories</h3>
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
  <!-- Collapsible: API rate limits -->
  <details class="gki-details" id="rate-limits">
    <summary class="gki-summary">Avoiding GitHub API Rate Limits</summary>
    <div class="gki-details-body">
      <div class="gki-callout gki-callout--warn">
        <svg class="gki-callout-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        <div>
          <p>If you're importing a large number of repositories, depending on size and commit history, you may encounter GitHub's hourly API rate limits. This can temporarily throttle other GitHub services used by your organization.</p>
          <p>To avoid this, additional members of your organization can connect to GitKraken Insights using a <a href="/gk-dev/gk-dev-organization/#roles">Lead role</a>. When multiple people are connected, the app distributes processing across their GitHub tokens to help avoid throttling.</p>
          <p>After the initial import is complete, rate limit issues are unlikely to recur.</p>
        </div>
      </div>
    </div>
  </details>
  <!-- Connect AI provider -->
  <h3 class="gki-subsection-title" id="connect-ai">Connect an AI Provider (Optional)</h3>
  <p>As of February 2026, GitKraken Insights only supports connections with Claude Code, Cursor and GitHub Copilot to enable AI insights.</p>
  <p>To enable AI Impact insights, connect your preferred AI provider:</p>
  <ol class="gki-steps">
    <li class="gki-step">
      <div class="gki-step-content">In GitKraken.dev, go to <a href="https://gitkraken.dev/insights/data-connections">Insights &gt; Data Connection</a>.</div>
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
  <!-- Confirm profile -->
  <h3 class="gki-subsection-title" id="confirm-profile">Confirm Your Profile Details</h3>
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
  <!-- Monitor import -->
  <h3 class="gki-subsection-title" id="monitor-import">Monitor Data Import Progress</h3>
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

<div class="exploration-notes">
<h2>Exploration Notes: Version 3 — Interactive with Plugin JS</h2>
<p><strong>Approach:</strong> Builds on V2's semantic HTML with <code>gki-</code> classes, adding interactive elements that leverage the plugin's externally-loaded JavaScript. Native HTML5 <code>&lt;details&gt;/&lt;summary&gt;</code> provides collapsible sections without JS. The GKI Docs Helper plugin provides all styling, JS features, and the 3-column page template.</p>
<p><strong>Key discovery — plugin solves JS limitation:</strong> WordPress strips inline <code>&lt;script&gt;</code> tags from post content imported via Git It Write. The GKI Docs Helper plugin bypasses this entirely by loading JS externally via <code>wp_enqueue_scripts</code>. All interactive features are now fully functional.</p>
<p><strong>Plugin features active:</strong> Auto-generated "On this page" TOC (right sidebar with scroll spy), card search/filter (type to filter the card grid), reading progress bar (gradient top bar), back-to-top button, smooth anchor scrolling, Parsedown cleanup, and the 3-column layout with category navigation.</p>
<p><strong>V3-specific features:</strong></p>
<ul>
  <li><strong>Native collapsible sections</strong> — the API Rate Limits warning uses <code>&lt;details&gt;/&lt;summary&gt;</code> (no JS required)</li>
  <li><strong>Searchable card grid</strong> — cards have <code>data-search</code> attributes; the plugin adds a filter input above the grid</li>
  <li><strong>Scroll-aware TOC</strong> — highlights the active section as you scroll, auto-scrolls the sidebar to keep it visible</li>
</ul>
<p><strong>Maintenance:</strong> Medium. Same as V2 (full HTML), but interactive features are zero-maintenance — they come from the plugin JS automatically. Adding headings to the page updates the TOC. Adding cards with <code>data-search</code> makes them searchable. No per-file JS needed.</p>
</div>
</div><!-- /.gki-page -->