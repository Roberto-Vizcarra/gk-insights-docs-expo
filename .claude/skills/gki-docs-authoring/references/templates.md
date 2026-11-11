# Page skeletons

Copy the skeleton, then copy real values from a sibling page. `<…>` marks
something to fill in. Delete any section the source material can't support:
don't pad it, and don't invent behaviour to fill it (CLAUDE.md, Content
Integrity). After writing, run `scripts/check_pages.py <file>` and
`python preview/build.py`.

---

## Metric page

File: `gk-insights/ai-adoption-<family-prefix>-<metric>.md`

````markdown
---
title: "<Metric Name>"
description: "How GitKraken Insights calculates and displays <Metric Name>."
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: gk-insights
custom_fields:
    card_color: green
    card_description: "Formula, calculation, interpretation, and FAQ for <Metric Name>"
    card_icon: <family icon, e.g. shield-check>
    nav_category: metrics
    nav_label: "<Metric Name>"
    nav_order: <family order + n, e.g. 45>
    nav_parent: ai-adoption-<family>-metrics
    page_type: content
---
<kbd>Last updated: <Month YYYY></kbd>

## <Metric Name>

> _<One-sentence definition, under 400 characters. Say "0–100" or "percentage" here if that is the range.>_

**Family:** <Family label> · **Cadence:** <…> · **Where it appears:** /insights/<path>, /insights/<path>

### At a glance

<What question it answers, in plain terms. Link to its nearest sibling metric.>

### Formula

```
<Metric> = <expression>

  <term> = <meaning>
```

* **<term>** — <meaning>

### How GitKraken Insights calculates it

**Step 1 — <Title>.** <…>

**Step 2 — <Title>.** <…>

**Aggregation.** <…>

### Why it matters

<…>

### How to read it

| Band | Meaning |
| --- | --- |
| **<Top band>** | <range or description> |
| **<…>** | <…> |
| **<Bottom band>** | <…> |

### Settings that affect it

* [**<Setting>**](/gk-insights/ai-adoption-settings#<setting-anchor>) (Settings → <tab>) — <effect>.

### How to improve it

* **<Lever>.** <…>

### Limitations and gotchas

* **<Gotcha>.** <…>

### FAQ

**Q: <Question>?**
A: <Answer.>

**Q: <Question>?**
A: <Answer.>

### Related metrics

| Metric | Relationship |
| --- | --- |
| [<Sibling>](/gk-insights/ai-adoption-<slug>) | <how they relate> |

### Where it appears

* **/insights/<path>** — <what the reader sees there>.

<figure>
  <img src="/wp-content/uploads/<existing-file>.png" class="help-center-img img-bordered" alt="<full description>" />
  <figcaption><caption></figcaption>
</figure>
````

Then: add the metric to its family index's overview table or list, and to the
**Related metrics** tables of the siblings it relates to. The Home metric map
and the family card grid pick it up automatically.

---

## Metric family sub-index

File: `gk-insights/ai-adoption-<family>-metrics.md`

```markdown
---
title: <Family> Metrics in the AI Adoption Dashboard
description: <one sentence listing the family's metrics>
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
status: GA
taxonomy:
    category: gk-insights
custom_fields:
    card_color: green
    card_description: <the metrics, comma-separated>
    card_icon: <new family icon>
    nav_category: metrics
    nav_label: <Family Label>
    nav_order: <next multiple of 10, e.g. 60>
    nav_parent: ai-adoption-metrics
    page_type: index
---
<kbd>Last updated: <Month YYYY></kbd>

<Intro: what this family measures. This is everything above the cards.>

---

## The <n> <family> metrics

| Metric | What it captures |
| --- | --- |
| [**<Metric>**](/gk-insights/ai-adoption-<slug>) | <…> |

---

## Read this family

1. <…>
```

A new family also needs a line on `ai-adoption-metrics.md` if that page
lists the families in prose.

---

## Section index (new top-level section)

Rare. It also needs a plugin change to place it in the nav (see SKILL.md).

```markdown
---
title: <Section Title>
description: <…>
product: GitKraken Insights
content_type: reference
audience: all
plan_required: GitKraken Insights
taxonomy:
    category: gk-insights
custom_fields:
    card_color: <purple|blue|green|amber|red>
    card_description: <…>
    card_icon: <tabler-icon>
    nav_category: <new-section-key>
    nav_label: <Section Label>
    nav_order: <e.g. 60>
    page_type: index
---
<kbd>Last updated: <Month YYYY></kbd>

<Intro.>
```

Child pages use the same `nav_category` and **no** `nav_parent`. The index
finds them through the fallback.

---

## How-to / connect / admin page

File: `gk-insights/ai-adoption-<section-prefix>-<topic>.md` (for
example `connect-<tool>`)

```markdown
---
title: Connect <Tool>
description: <what it connects, what it powers, what the page covers>
product: GitKraken Insights
content_type: how-to
audience: admin
plan_required: GitKraken Insights
status: GA
taxonomy:
    category: gk-insights
custom_fields:
    card_color: blue
    card_description: <short subtitle; searchable>
    card_icon: <tabler-icon, e.g. brand-github>
    nav_category: connect-your-data
    nav_label: <Tool>
    nav_order: <between siblings>
    page_type: content
---
<kbd>Last updated: <Month YYYY></kbd>

<One or two sentences: what connecting this does and which metrics it powers.>

> **Before you start:** <prerequisite, with a link>.

---

## <Task-based heading>

1. In gitkraken.dev, open **Insights → Settings → Data Connections**.
2. <…>

> **Heads up:** <a warning the reader must not miss>.

## <Next task>

…
```

Admin pages use `nav_category: admin` and `card_color: red`. Getting Started
pages use `getting-started` and `purple`. A screenshot before the first H2
(not after a numbered list) becomes the page's hero figure.

---

## Playbook

File: `gk-insights/ai-adoption-playbook-<topic>.md`

```markdown
---
title: <Task-phrased title, e.g. Interpret a High CFR Week>
description: <…>
product: GitKraken Insights
content_type: how-to
audience: <…>
plan_required: GitKraken Insights
taxonomy:
    category: gk-insights
custom_fields:
    card_color: amber
    card_description: <…>
    card_icon: <tabler-icon>
    nav_category: playbooks
    nav_label: <Short Label>
    nav_order: <next multiple of 10>
    page_type: content
---
<kbd>Last updated: <Month YYYY></kbd>

> _<The situation in one italic sentence: becomes the lede.>_

## The problem

<…>

## Where to look

<…>

## What to do

### Step 1 — <Action> (10 min)

<…>

### Step 2 — <Action> (30 min)

<…>

## If the cause is X or Y

### Pattern: <cause A>

<…>

### If <cause B> dominates

<…>

## Common mistakes

* **<Mistake>.** <…>

## Related pages

| Page | Why |
| --- | --- |
| [<Page>](/gk-insights/ai-adoption-<slug>) | <…> |
| [<Page>](/gk-insights/ai-adoption-<slug>) | <…> |
```

With two or more rows of links, the table gets the related-table treatment.

Check the frontmatter of an existing playbook (`content_type`, `audience`,
`title` format) before you copy this. Match what's there.
