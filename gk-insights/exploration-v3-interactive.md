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
    category: gk-insights
---

<style>
/* ============================================================
   GitKraken Insights - V3 Interactive HTML/CSS/JS
   ============================================================ */
:root {
  --gki-accent: #0D9488;
  --gki-accent-hover: #0F766E;
  --gki-accent-light: #CCFBF1;
  --gki-accent-bg: #F0FDFA;
  --gki-text: #e4e3e0;
  --gki-text-secondary: #a8a5a0;
  --gki-text-muted: #6b6860;
  --gki-border: #3a3a40;
  --gki-border-strong: #4a4a50;
  --gki-bg: #1b1b1f;
  --gki-bg-subtle: #232328;
  --gki-bg-offset: #2a2a30;
  --gki-info-bg: #1a2332;
  --gki-info-border: #2a4060;
  --gki-info-text: #7cb3f5;
  --gki-warn-bg: #2a2210;
  --gki-warn-border: #5a4a20;
  --gki-warn-text: #e8b84a;
  --gki-tip-bg: #162a26;
  --gki-tip-border: #1a4a40;
  --gki-tip-text: #5ec4b0;
  --gki-radius: 8px;
  --gki-radius-lg: 12px;
  --gki-shadow-sm: 0 1px 2px rgba(0,0,0,0.3);
  --gki-shadow: 0 1px 3px rgba(0,0,0,0.4), 0 1px 2px rgba(0,0,0,0.2);
  --gki-shadow-md: 0 4px 6px -1px rgba(0,0,0,0.4), 0 2px 4px -2px rgba(0,0,0,0.3);
  --gki-font: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  --gki-font-mono: "SF Mono", "Cascadia Code", "Fira Code", Consolas, monospace;
}
/* ---------- Layout: two-column with sticky TOC ---------- */
.gki-page * { box-sizing: border-box; }
.gki-page {
  font-family: var(--gki-font);
  color: var(--gki-text);
  line-height: 1.7;
  max-width: 1100px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 220px;
  gap: 2.5rem;
  align-items: start;
}
.gki-content {
  min-width: 0;
}
/* ---------- Sticky TOC sidebar ---------- */
.gki-toc {
  position: sticky;
  top: 2rem;
  max-height: calc(100vh - 4rem);
  overflow-y: auto;
  padding: 1rem 0;
  border-left: 1px solid var(--gki-border);
  padding-left: 1rem;
}
.gki-toc-title {
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--gki-text-muted);
  margin: 0 0 0.75rem 0;
}
.gki-toc-list {
  list-style: none;
  padding: 0;
  margin: 0;
}
.gki-toc-list li {
  margin-bottom: 0.15rem;
}
.gki-toc-list a {
  display: block;
  font-size: 0.8rem;
  line-height: 1.45;
  color: var(--gki-text-muted);
  text-decoration: none;
  padding: 0.2rem 0 0.2rem 0.6rem;
  border-left: 2px solid transparent;
  transition: color 0.15s ease, border-color 0.15s ease;
}
.gki-toc-list a:hover {
  color: var(--gki-text-secondary);
}
.gki-toc-list a.gki-toc-active {
  color: var(--gki-accent);
  border-left-color: var(--gki-accent);
  font-weight: 500;
}
.gki-toc-list a[data-depth="3"] {
  padding-left: 1.2rem;
  font-size: 0.77rem;
}
/* Hide TOC on narrow screens */
@media (max-width: 860px) {
  .gki-page {
    grid-template-columns: 1fr;
  }
  .gki-toc {
    display: none;
  }
}
/* ---------- Progress bar ---------- */
.gki-progress {
  position: fixed;
  top: 0;
  left: 0;
  width: 0%;
  height: 3px;
  background: var(--gki-accent);
  z-index: 9999;
  transition: width 0.1s linear;
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
.gki-breadcrumb a:hover { text-decoration: underline; }
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
/* ---------- Intro ---------- */
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
.gki-req-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem 2rem;
}
@media (max-width: 600px) {
  .gki-req-grid { grid-template-columns: 1fr; }
}
.gki-req-item {
  display: flex;
  align-items: baseline;
  gap: 0.4rem;
  font-size: 0.93rem;
  line-height: 1.55;
  padding: 0.25rem 0;
}
.gki-req-label { font-weight: 600; color: var(--gki-text); white-space: nowrap; }
.gki-req-value { color: var(--gki-text-secondary); }
.gki-req-value a { color: var(--gki-accent); text-decoration: none; }
.gki-req-value a:hover { text-decoration: underline; }
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
/* ---------- Sections ---------- */
.gki-section {
  margin-bottom: 2.5rem;
  padding-bottom: 2.5rem;
  border-bottom: 1px solid var(--gki-border);
}
.gki-section:last-of-type { border-bottom: none; }
.gki-section-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--gki-text);
  margin: 0 0 1rem 0;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid var(--gki-accent);
  display: inline-block;
  scroll-margin-top: 1.5rem;
}
.gki-subsection-title {
  font-size: 1.2rem;
  font-weight: 600;
  color: var(--gki-text);
  margin: 2rem 0 0.75rem 0;
  scroll-margin-top: 1.5rem;
}
/* ---------- Collapsible sections ---------- */
.gki-collapsible {
  border: 1px solid var(--gki-border);
  border-radius: var(--gki-radius);
  margin: 1.25rem 0;
  overflow: hidden;
}
.gki-collapsible-toggle {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 0.85rem 1.15rem;
  background: var(--gki-bg-offset);
  border: none;
  cursor: pointer;
  font-family: var(--gki-font);
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--gki-text);
  text-align: left;
  gap: 0.75rem;
  transition: background 0.15s ease;
}
.gki-collapsible-toggle:hover {
  background: var(--gki-border);
}
.gki-collapsible-toggle:focus-visible {
  outline: 2px solid var(--gki-accent);
  outline-offset: -2px;
}
.gki-collapsible-chevron {
  flex-shrink: 0;
  width: 16px;
  height: 16px;
  color: var(--gki-text-muted);
  transition: transform 0.2s ease;
}
.gki-collapsible.gki-open .gki-collapsible-chevron {
  transform: rotate(180deg);
}
.gki-collapsible-body {
  display: none;
  padding: 1rem 1.15rem;
}
.gki-collapsible.gki-open .gki-collapsible-body {
  display: block;
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
.gki-callout p { margin: 0; }
.gki-callout p + p { margin-top: 0.5rem; }
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
.gki-callout a { font-weight: 600; text-decoration: underline; }
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
.gki-step-content strong { color: var(--gki-text); }
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
.gki-list li strong { color: var(--gki-text); }
/* ---------- Card grid with search ---------- */
.gki-related { margin-top: 3rem; }
.gki-related-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 1.25rem;
}
.gki-related-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--gki-text);
  margin: 0;
}
.gki-card-search {
  font-family: var(--gki-font);
  font-size: 0.85rem;
  padding: 0.4rem 0.75rem 0.4rem 2rem;
  border: 1px solid var(--gki-border);
  border-radius: 6px;
  background: var(--gki-bg) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23A8A29E' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E") no-repeat 0.65rem center;
  color: var(--gki-text);
  outline: none;
  width: 180px;
  transition: border-color 0.15s ease;
}
.gki-card-search:focus {
  border-color: var(--gki-accent);
}
.gki-card-search::placeholder {
  color: var(--gki-text-muted);
}
.gki-card-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}
@media (max-width: 768px) {
  .gki-card-grid { grid-template-columns: 1fr; }
}
@media (min-width: 769px) and (max-width: 960px) {
  .gki-card-grid { grid-template-columns: repeat(2, 1fr); }
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
.gki-card.gki-card-hidden {
  display: none;
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
/* ---------- No results message ---------- */
.gki-no-results {
  display: none;
  grid-column: 1 / -1;
  text-align: center;
  color: var(--gki-text-muted);
  font-size: 0.9rem;
  padding: 2rem 0;
}
.gki-no-results.gki-visible {
  display: block;
}
/* ---------- Back to top ---------- */
.gki-back-top {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  width: 40px;
  height: 40px;
  background: var(--gki-accent);
  color: #fff;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: var(--gki-shadow-md);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s ease, transform 0.2s ease;
  z-index: 100;
}
.gki-back-top:hover {
  transform: scale(1.1);
}
.gki-back-top:focus-visible {
  outline: 2px solid var(--gki-accent);
  outline-offset: 2px;
}
.gki-back-top.gki-visible {
  opacity: 1;
  pointer-events: auto;
}
/* ---------- Fix WP/Parsedown empty paragraphs ---------- */
.gki-page p:empty { display: none !important; margin: 0 !important; padding: 0 !important; line-height: 0 !important; }
.gki-page { max-width: none; }
.gki-card-grid { display: grid !important; grid-template-columns: repeat(2, 1fr) !important; gap: 1rem !important; }
.gki-card { display: flex !important; background: var(--gki-bg-subtle) !important; border: 1px solid var(--gki-border) !important; border-radius: var(--gki-radius) !important; padding: 1.15rem 1.25rem !important; }
.gki-breadcrumb { display: flex !important; }
.gki-req-grid { display: grid !important; grid-template-columns: 1fr 1fr !important; }
.gki-steps { list-style: none !important; padding: 0 !important; counter-reset: gki-step !important; }
.gki-step { display: flex !important; counter-increment: gki-step !important; }
.gki-step::before { content: counter(gki-step) !important; display: flex !important; align-items: center !important; justify-content: center !important; width: 32px !important; height: 32px !important; background: var(--gki-accent) !important; color: #fff !important; font-size: 0.85rem !important; font-weight: 700 !important; border-radius: 50% !important; flex-shrink: 0 !important; margin-top: 0.1rem !important; }
.gki-callout { display: flex !important; }
</style>

<!-- Progress bar -->
<div class="gki-progress" id="gkiProgress"></div>

<div class="gki-page">
<!-- ============================================================
     Main content column
     ============================================================ -->
<div class="gki-content">
<!-- Breadcrumb -->
<nav class="gki-breadcrumb" aria-label="Breadcrumb"><a href="/gk-insights/">GitKraken Insights</a><span class="gki-crumb-sep" aria-hidden="true">/</span><span class="gki-crumb-current" aria-current="page">Getting Started</span></nav>
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
     Related Pages with Search
     ============================================================ -->
<section class="gki-related" id="explore">
  <div class="gki-related-header">
    <h2 class="gki-related-title">Explore GitKraken Insights</h2>
    <input type="text" class="gki-card-search" id="gkiCardSearch" placeholder="Filter pages..." aria-label="Filter related pages">
  </div>
  <div class="gki-card-grid" id="gkiCardGrid">
    <a href="/gk-insights/gk-insights-dora-metrics" class="gki-card" data-search="dora deploy frequency change lead time mean time repair defect rate">
      <div>
        <div class="gki-card-title">
          DORA Metrics
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Deploy Frequency, Change Lead Time, Mean Time to Repair, and Defect Rate.</p>
      </div>
    </a>
    <a href="/gk-insights/gk-insights-pr-metrics" class="gki-card" data-search="pull request cycle time first response open time pr size review comments merged abandoned">
      <div>
        <div class="gki-card-title">
          Pull Request Metrics
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Cycle Time, First Response Time, Open Time, PR Size, and review activity.</p>
      </div>
    </a>
    <a href="/gk-insights/gk-insights-ai-impact-metrics" class="gki-card" data-search="ai impact prompt acceptance tab duplicated code rework active users suggestions copilot cursor claude">
      <div>
        <div class="gki-card-title">
          AI Impact Metrics
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Prompt Acceptance Rate, Duplicated Code, Code Rework, and AI usage.</p>
      </div>
    </a>
    <a href="/gk-insights/gk-insights-code-quality-metrics" class="gki-card" data-search="code quality bug work percent documentation test change rate operation">
      <div>
        <div class="gki-card-title">
          Code Quality Metrics
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Bug Work Percent, Documentation and Test Percent, and Code Change Rate.</p>
      </div>
    </a>
    <a href="/gk-insights/gk-insights-velocity-metrics" class="gki-card" data-search="velocity delivery consistency commit count estimated coding hours">
      <div>
        <div class="gki-card-title">
          Velocity Metrics
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Commit Count and Estimated Coding Hours for delivery consistency.</p>
      </div>
    </a>
    <a href="/gk-insights/gk-insights-dashboard-management" class="gki-card" data-search="dashboard configure filters layout views team management">
      <div>
        <div class="gki-card-title">
          Dashboard Management
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Configure filters, layouts, and dashboard views for your team.</p>
      </div>
    </a>
    <a href="/gk-insights/gk-insights-metric-settings" class="gki-card" data-search="metric settings thresholds date ranges calculation release tracking">
      <div>
        <div class="gki-card-title">
          Metric Settings
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Customize thresholds, date ranges, and metric calculation settings.</p>
      </div>
    </a>
    <a href="/gk-insights/gk-insights-faq" class="gki-card" data-search="faq frequently asked questions troubleshooting help setup data sources">
      <div>
        <div class="gki-card-title">
          FAQ
          <svg class="gki-card-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <p class="gki-card-desc">Common questions about setup, data sources, metrics, and troubleshooting.</p>
      </div>
    </a>
    <div class="gki-no-results" id="gkiNoResults">No matching pages found.</div>
  </div>
</section>
<!-- ============================================================
     Request Access
     ============================================================ -->
<section class="gki-section" id="request-access">
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
  <div class="gki-collapsible" id="rate-limits">
    <button class="gki-collapsible-toggle" aria-expanded="false">
      <span>Avoiding GitHub API Rate Limits</span>
      <svg class="gki-collapsible-chevron" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <div class="gki-collapsible-body">
      <div class="gki-callout gki-callout--warn">
        <svg class="gki-callout-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        <div>
          <p>If you're importing a large number of repositories, depending on size and commit history, you may encounter GitHub's hourly API rate limits. This can temporarily throttle other GitHub services used by your organization.</p>
          <p>To avoid this, additional members of your organization can connect to GitKraken Insights using a <a href="/gk-dev/gk-dev-organization/#roles">Lead role</a>. When multiple people are connected, the app distributes processing across their GitHub tokens to help avoid throttling.</p>
          <p>After the initial import is complete, rate limit issues are unlikely to recur.</p>
        </div>
      </div>
    </div>
  </div>
  <!-- Connect AI provider -->
  <h3 class="gki-subsection-title" id="connect-ai">Connect an AI Provider (Optional)</h3>
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
</div><!-- /.gki-content -->
<!-- ============================================================
     TOC sidebar (populated by JS)
     ============================================================ -->
<aside class="gki-toc" aria-label="Table of contents">
  <div class="gki-toc-title">On this page</div>
  <ul class="gki-toc-list" id="gkiTocList">
    <!-- JS-generated -->
  </ul>
</aside>
</div><!-- /.gki-page -->
<!-- Back to top button -->
<button class="gki-back-top" id="gkiBackTop" aria-label="Back to top">
  <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><path d="M8 12V4m0 0L4 8m4-4l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
</button>
<!-- ============================================================
     JavaScript: TOC, scroll tracking, collapsible, search, back-to-top
     ============================================================ -->
<script>
(function() {
  'use strict';
  // --- Build TOC from section/subsection headings ---
  var tocList = document.getElementById('gkiTocList');
  var headings = document.querySelectorAll('.gki-content .gki-section-title, .gki-content .gki-subsection-title');
  var tocItems = [];
  headings.forEach(function(h) {
    var id = h.id || h.closest('[id]')?.id;
    if (!id) {
      id = h.textContent.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
      h.id = id;
    }
    var depth = h.classList.contains('gki-subsection-title') ? 3 : 2;
    var li = document.createElement('li');
    var a = document.createElement('a');
    a.href = '#' + id;
    a.textContent = h.textContent.trim();
    a.setAttribute('data-depth', depth);
    a.addEventListener('click', function(e) {
      e.preventDefault();
      document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    li.appendChild(a);
    tocList.appendChild(li);
    tocItems.push({ id: id, el: h, link: a });
  });
  // --- Active TOC highlight on scroll ---
  function updateActiveToc() {
    var scrollY = window.scrollY || window.pageYOffset;
    var active = null;
    for (var i = tocItems.length - 1; i >= 0; i--) {
      if (tocItems[i].el.getBoundingClientRect().top <= 80) {
        active = tocItems[i];
        break;
      }
    }
    tocItems.forEach(function(item) {
      item.link.classList.toggle('gki-toc-active', item === active);
    });
  }
  // --- Progress bar ---
  var progressBar = document.getElementById('gkiProgress');
  function updateProgress() {
    var docHeight = document.documentElement.scrollHeight - window.innerHeight;
    var scrolled = window.scrollY || window.pageYOffset;
    var pct = docHeight > 0 ? (scrolled / docHeight) * 100 : 0;
    progressBar.style.width = pct + '%';
  }
  // --- Back to top ---
  var backTopBtn = document.getElementById('gkiBackTop');
  function updateBackTop() {
    var scrolled = window.scrollY || window.pageYOffset;
    backTopBtn.classList.toggle('gki-visible', scrolled > 400);
  }
  backTopBtn.addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
  // --- Scroll listener (debounced) ---
  var scrollTicking = false;
  window.addEventListener('scroll', function() {
    if (!scrollTicking) {
      window.requestAnimationFrame(function() {
        updateActiveToc();
        updateProgress();
        updateBackTop();
        scrollTicking = false;
      });
      scrollTicking = true;
    }
  }, { passive: true });
  // Initial call
  updateActiveToc();
  updateProgress();
  // --- Collapsible sections ---
  document.querySelectorAll('.gki-collapsible-toggle').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var parent = btn.closest('.gki-collapsible');
      var isOpen = parent.classList.toggle('gki-open');
      btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  });
  // --- Card search/filter ---
  var searchInput = document.getElementById('gkiCardSearch');
  var cardGrid = document.getElementById('gkiCardGrid');
  var cards = cardGrid.querySelectorAll('.gki-card');
  var noResults = document.getElementById('gkiNoResults');
  searchInput.addEventListener('input', function() {
    var q = this.value.toLowerCase().trim();
    var visibleCount = 0;
    cards.forEach(function(card) {
      var searchText = (card.getAttribute('data-search') || '') + ' ' +
                       (card.textContent || '');
      var match = !q || searchText.toLowerCase().indexOf(q) !== -1;
      card.classList.toggle('gki-card-hidden', !match);
      if (match) visibleCount++;
    });
    noResults.classList.toggle('gki-visible', visibleCount === 0 && q.length > 0);
  });
})();
</script>
<div class="exploration-notes" style="margin-top:3rem; padding:1.5rem; background:#2a2a30; border:1px solid #3a3a40; color:#a8a5a0; border-radius:8px;">
<h2 style="font-size:1.1rem; margin-top:0; color:#e4e3e0;">Exploration Notes: Version 3 — Interactive HTML/CSS/JS</h2>
<p><strong>Approach:</strong> Builds on V2's HTML/CSS card layout and adds embedded JavaScript for interactive features: an auto-generated sticky table of contents with active-section highlighting, a reading progress bar, collapsible info sections, a search/filter for the card grid, smooth scroll navigation, and a back-to-top button.</p>
<p><strong>What this tests:</strong> The absolute ceiling of what a single .md file processed through Git It Write can deliver inside WordPress. This is everything you can do without touching the WP theme or deploying a separate frontend. If this level of interactivity works in your WP setup, it tells you how far the "enhanced Markdown" approach can stretch.</p>
<p><strong>New features over V2:</strong></p>
<ul style="margin:0.25rem 0 0.75rem 1.25rem; font-size:0.93rem; line-height:1.65;">
  <li><strong>Sticky TOC sidebar</strong> — auto-generated from headings, highlights current section as you scroll, disappears on mobile</li>
  <li><strong>Reading progress bar</strong> — thin teal bar at the top of the viewport showing scroll position</li>
  <li><strong>Collapsible sections</strong> — the API Rate Limits warning is collapsed by default (click to expand). Useful for secondary info that shouldn't clutter the main flow</li>
  <li><strong>Card search/filter</strong> — type in the search box above the card grid to filter related pages by keyword. "No results" message if nothing matches</li>
  <li><strong>Smooth scroll</strong> — clicking TOC links scrolls smoothly to the target section</li>
  <li><strong>Back to top button</strong> — appears after scrolling past the fold</li>
</ul>
<p><strong>Limitations:</strong> Same as V2 — the WP page template still controls header/footer/sidebar. Additionally: the two-column layout (content + TOC) may conflict with the WP theme's own sidebar if one exists — test in your theme. JavaScript must execute within the WP post content area, which some security plugins or caching layers may strip. Progress bar uses <code>position: fixed</code> which works relative to the viewport, not the WP post area — may overlap WP admin bar if logged in.</p>
<p><strong>Maintenance:</strong> High. This is effectively a single-page app embedded in a Markdown file. Changes to the layout, interactivity, or component styles require editing HTML + CSS + JS. Shared JS/CSS would need to live in a WP plugin or theme customization. Each docs page would need its own copy of this code (or a shared script/style loaded via WP).</p>
<p><strong>Key question this version answers:</strong> Is the result good enough to justify the maintenance burden, or does it make a stronger case for moving to a platform where these features are built in?</p>
</div>