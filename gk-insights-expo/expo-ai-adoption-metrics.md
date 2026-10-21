---
title: AI Adoption Metrics
description: Five families of engineering metrics in GitKraken Insights — adoption and agentic, output, flow, DORA, and AI impact and cost.
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
status: GA
taxonomy:
    category: insights-expo
custom_fields:
    card_color: green
    card_description: 21 metrics across five families
    card_icon: chart-dots-3
    nav_category: metrics
    nav_label: Metrics
    nav_order: 30
    page_type: index
---

<kbd>Last updated: October 2026</kbd>

GitKraken Insights tracks 21 metrics across five families. Each family measures a different dimension of your team's AI adoption and delivery health. Select a family below to see every metric it contains.

---

## Working with charts

Every chart card in GitKraken Insights has a **⋯** (chart options) menu in its header. The menu lists only the actions that chart supports:

* **Export graph data** — downloads the chart's data as a CSV file, as the chart displays it. A header block at the top of the file names the chart, the date you exported it, the filters that were active, and the chart's units, for example *bugs per developer*. Units that are a rate per period follow the chart's granularity. Values are rounded to two decimal places. When the chart compares against the previous period, a **Note** row explains that previous-period values are aligned to the current period column by column, so their column headings are current-period dates.
* **Add annotation** — opens the dialog for creating an annotation: a marker for an event such as a tool rollout. Annotations belong to the whole organization and appear on every time-series chart.
* **View** — on breakdown charts, picks the dimension the chart is split by, such as Team or Department. The **Total / Trend** switch beside the menu toggles the same chart between totals and a trend over time.

<figure>
  <img src="/wp-content/uploads/gki-chart-options-menu-01-v3@2x.png" class="help-center-img img-bordered" alt="The Cycle Time chart card in GitKraken Insights, comparing the current and previous period in hours, with its more-options menu open listing Add annotation and Export graph data" />
  <figcaption>The ⋯ menu on a chart card, with Add annotation and Export graph data.</figcaption>
</figure>

### Choose a granularity

The **Granularity** control beside the timeframe sets how charts group their data over time: **Auto**, **Hourly**, **Daily**, **Weekly**, or **Monthly**. Auto picks a granularity that suits the selected timeframe and shows it in parentheses, for example **Auto (Weekly)**. If you pick a granularity the current timeframe can't show readably, Insights adjusts the timeframe to keep the chart readable. When you click a chart point, the drill-down covers just that bucket. The control doesn't appear on the AI Impact, CapEx/OpEx, or Repositories pages.

<figure>
  <img src="/wp-content/uploads/gki-granularity-control-01-v3@2x.png" class="help-center-img img-bordered" alt="The filter bar of an Insights page with the Granularity menu open, listing Auto (Weekly), Hourly, Daily, Weekly and Monthly under a search box, beside the Timeframe, Filters and Teams controls and Export CSV, over the top of the Cycle Time charts" />
  <figcaption>The Granularity menu, open beside the timeframe.</figcaption>
</figure>
