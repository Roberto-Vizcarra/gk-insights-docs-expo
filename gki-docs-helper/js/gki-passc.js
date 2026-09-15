/* ============================================================
   GKI Docs Helper — PASS C behaviour
   ------------------------------------------------------------
   Three modules:

     1. Command palette  — Ctrl/Cmd-K over the existing server-built
                           search index. Replaces the search input as
                           the visible affordance; the original input
                           stays in the DOM so the legacy search code
                           keeps working if this file never loads.

     2. Instruments      — one per metric page that earns one. A
                           sandbox only where the formula has a tunable
                           input with a non-obvious result; otherwise a
                           proportion bar, a sequence timeline, or a
                           precedence table.

     3. Entry reveal     — IntersectionObserver, cards and section
                           blocks only. Never a scroll listener.

   Every formula here is transcribed from the page it appears on.
   Where the product exposes a weight as an env var, the sandbox uses
   the documented default and says so in its footnote.
   ============================================================ */
(function () {
  'use strict';

  var TIERS = [
    { min: 80, name: 'Power User', varName: '--gki-tier-power' },
    { min: 55, name: 'Regular',    varName: '--gki-tier-regular' },
    { min: 25, name: 'Explorer',   varName: '--gki-tier-explorer' },
    { min: 0,  name: 'Emerging',   varName: '--gki-tier-emerging' }
  ];

  function tierFor(score) {
    for (var i = 0; i < TIERS.length; i++) {
      if (score >= TIERS[i].min) return TIERS[i];
    }
    return TIERS[TIERS.length - 1];
  }

  /* ---------- tiny DOM helpers ---------- */
  function h(tag, attrs, children) {
    var node = document.createElement(tag);
    if (attrs) {
      Object.keys(attrs).forEach(function (k) {
        if (k === 'class') node.className = attrs[k];
        else if (k === 'text') node.textContent = attrs[k];
        else node.setAttribute(k, attrs[k]);
      });
    }
    (children || []).forEach(function (c) {
      if (c) node.appendChild(typeof c === 'string' ? document.createTextNode(c) : c);
    });
    return node;
  }

  var uid = 0;
  function nextId(prefix) { uid += 1; return prefix + '-' + uid; }

  /**
   * A labelled range control. Returns { row, input, setLabel }.
   */
  function slider(label, opts) {
    var id = nextId('gki-ctrl');
    var val = h('span', { class: 'gki-ctrl-val' });
    var input = h('input', {
      type: 'range',
      id: id,
      min: String(opts.min),
      max: String(opts.max),
      step: String(opts.step),
      value: String(opts.value)
    });
    var row = h('div', { class: 'gki-ctrl' }, [
      h('div', { class: 'gki-ctrl-top' }, [
        h('label', { for: id, text: label }),
        val
      ]),
      input
    ]);
    return {
      row: row,
      input: input,
      value: function () { return parseFloat(input.value); },
      setLabel: function (t) { val.textContent = t; }
    };
  }

  function groupLabel(text) {
    return h('div', { class: 'gki-ctrl-group', text: text });
  }

  function instrumentHead(title, note) {
    return h('div', { class: 'gki-inst-head' }, [
      h('span', { class: 'gki-inst-title', text: title }),
      note ? h('span', { class: 'gki-inst-note', text: note }) : null
    ]);
  }

  function sourceNote(text) {
    return h('p', { class: 'gki-inst-source', text: text });
  }

  /**
   * Big number + unit + tier pill.
   */
  function readout(unit) {
    var value = h('span', { class: 'gki-readout-value', text: '0' });
    var band = h('span', { class: 'gki-band' });
    var wrap = h('div', { class: 'gki-readout' }, [
      value,
      unit ? h('span', { class: 'gki-readout-unit', text: unit }) : null,
      band
    ]);
    return {
      node: wrap,
      band: band,
      set: function (v) { value.textContent = v; },
      setBand: function (tier) {
        if (!tier) { band.style.display = 'none'; return; }
        band.style.display = '';
        band.textContent = tier.name;
        band.style.color = 'var(' + tier.varName + ')';
      }
    };
  }

  /**
   * The 0-100 tier scale with a position marker. Band widths are
   * proportional to their real numeric width.
   */
  function tierScale() {
    var track = h('div', { class: 'gki-tier-track' });
    track.style.gridTemplateColumns = '24fr 30fr 25fr 21fr';

    var order = ['--gki-tier-emerging', '--gki-tier-explorer', '--gki-tier-regular', '--gki-tier-power'];
    var segs = order.map(function (v) {
      var s = h('span');
      s.style.background = 'var(' + v + ')';
      track.appendChild(s);
      return s;
    });

    var marker = h('div', { class: 'gki-tier-marker' });
    var wrap = h('div', { class: 'gki-tier-scale' }, [
      track,
      marker,
      h('div', { class: 'gki-tier-labels' }, [
        h('span', { text: '0' }),
        h('span', { text: '25' }),
        h('span', { text: '55' }),
        h('span', { text: '80' }),
        h('span', { text: '100' })
      ])
    ]);

    return {
      node: wrap,
      set: function (score) {
        marker.style.left = Math.max(0, Math.min(100, score)) + '%';
        var tier = tierFor(score);
        segs.forEach(function (s, i) {
          s.setAttribute('data-current', String(order[i] === tier.varName));
        });
      }
    };
  }

  function derivedRow(label) {
    var b = h('b');
    var row = h('div', {}, [h('span', { text: label }), b]);
    return { node: row, set: function (v) { b.textContent = v; } };
  }

  /**
   * Assemble a two-column sandbox: results left, controls right.
   */
  function sandbox(mount, title, note, left, controls, source) {
    mount.appendChild(instrumentHead(title, note));
    mount.appendChild(h('div', { class: 'gki-sandbox' }, [
      h('div', {}, left),
      h('div', { class: 'gki-controls' }, controls)
    ]));
    if (source) mount.appendChild(sourceNote(source));
  }

  /* =========================================================
     INSTRUMENT: Maturity Factor
     ScoreAfterMaturity = ScoreBeforeMaturity x MaturityFactor
     ========================================================= */
  function instMaturity(mount) {
    var out = readout('ceiling');
    var scale = tierScale();
    var dRaw = derivedRow('A developer at org P90 on every factor scores');
    var dMid = derivedRow('A mid-range developer (raw 60) scores');
    var dLow = derivedRow('A light user (raw 30) scores');

    var mf = slider('Maturity Factor', { min: 0.5, max: 1, step: 0.05, value: 0.75 });

    function update() {
      var f = mf.value();
      var ceiling = Math.round(100 * f);
      out.set(String(ceiling));
      out.setBand(tierFor(ceiling));
      scale.set(ceiling);
      mf.setLabel(f.toFixed(2));
      dRaw.set(String(ceiling));
      dMid.set(String(Math.round(60 * f)));
      dLow.set(String(Math.round(30 * f)));
    }

    mf.input.addEventListener('input', update);

    sandbox(mount, 'What the factor does to every score',
      'Tier bands are fixed; the scores move',
      [out.node, scale.node, h('div', { class: 'gki-derived' }, [dRaw.node, dMid.node, dLow.node])],
      [groupLabel('Org setting'), mf.row],
      'Models the documented relationship ScoreAfterMaturity = ScoreBeforeMaturity x MaturityFactor. Default 0.75.');

    update();
  }

  /* =========================================================
     INSTRUMENT: Agent Adoption Score
     Adoption = min(Primary + 0.25 x Cursor, 100) x MaturityFactor
     ========================================================= */
  function instAdoption(mount) {
    var out = readout('/ 100');
    var scale = tierScale();
    var dPrimary = derivedRow('Primary (Claude and Codex)');
    var dPre = derivedRow('Before maturity scaling');
    var dCeiling = derivedRow('Ceiling at this Maturity Factor');

    var f1 = slider('Daily use', { min: 0, max: 1, step: 0.01, value: 0.8 });
    var f2 = slider('Hourly spread', { min: 0, max: 1, step: 0.01, value: 0.7 });
    var f3 = slider('Prompts', { min: 0, max: 1, step: 0.01, value: 0.65 });
    var f4 = slider('Output tokens', { min: 0, max: 1, step: 0.01, value: 0.6 });
    var cur = slider('Cursor score', { min: 0, max: 100, step: 1, value: 30 });
    var mf = slider('Maturity Factor', { min: 0.5, max: 1, step: 0.05, value: 0.75 });

    function update() {
      var factors = [f1, f2, f3, f4];
      var primary = (f1.value() + f2.value() + f3.value() + f4.value()) / 4 * 100;
      var pre = Math.min(primary + 0.25 * cur.value(), 100);
      var score = Math.round(pre * mf.value());

      out.set(String(score));
      out.setBand(tierFor(score));
      scale.set(score);

      dPrimary.set(primary.toFixed(0));
      dPre.set(pre.toFixed(0));
      dCeiling.set(String(Math.round(100 * mf.value())));

      factors.forEach(function (s) { s.setLabel(s.value().toFixed(2)); });
      cur.setLabel(String(cur.value()));
      mf.setLabel(mf.value().toFixed(2));
    }

    [f1, f2, f3, f4, cur, mf].forEach(function (s) {
      s.input.addEventListener('input', update);
    });

    sandbox(mount, 'Score sandbox', 'Each factor is normalized against org P90',
      [out.node, scale.node, h('div', { class: 'gki-derived' }, [dPrimary.node, dPre.node, dCeiling.node])],
      [
        groupLabel('Primary factors'), f1.row, f2.row, f3.row, f4.row,
        groupLabel('Secondary and scaling'), cur.row, mf.row
      ],
      'The four factors are weighted equally here. In the product the per-provider weights are set by SCORE_WEIGHT_* and are not exposed in Settings, so a tuned org may differ.');

    update();
  }

  /* =========================================================
     INSTRUMENT: AI Tier
     composite = wA x Adoption + wG x Agentic + wO x OutputNorm
     weights renormalized to sum to 1.0
     ========================================================= */
  function instAiTier(mount) {
    var out = readout('composite');
    var scale = tierScale();
    var dNorm = derivedRow('Weights after renormalizing');

    var adoption = slider('Adoption Score', { min: 0, max: 100, step: 1, value: 62 });
    var agentic = slider('Agentic Score', { min: 0, max: 100, step: 1, value: 48 });
    var output = slider('Output (normalized)', { min: 0, max: 100, step: 1, value: 55 });

    var wA = slider('Adoption weight', { min: 0, max: 1, step: 0.05, value: 0.5 });
    var wG = slider('Agentic weight', { min: 0, max: 1, step: 0.05, value: 0.2 });
    var wO = slider('Output weight', { min: 0, max: 1, step: 0.05, value: 0.3 });

    function update() {
      var sum = wA.value() + wG.value() + wO.value();
      var nA, nG, nO;
      if (sum <= 0) { nA = nG = nO = 1 / 3; } else {
        nA = wA.value() / sum; nG = wG.value() / sum; nO = wO.value() / sum;
      }

      var composite = Math.round(nA * adoption.value() + nG * agentic.value() + nO * output.value());
      out.set(String(composite));
      out.setBand(tierFor(composite));
      scale.set(composite);

      dNorm.set(nA.toFixed(2) + ' / ' + nG.toFixed(2) + ' / ' + nO.toFixed(2));

      [adoption, agentic, output].forEach(function (s) { s.setLabel(String(s.value())); });
      [wA, wG, wO].forEach(function (s) { s.setLabel(s.value().toFixed(2)); });
    }

    [adoption, agentic, output, wA, wG, wO].forEach(function (s) {
      s.input.addEventListener('input', update);
    });

    sandbox(mount, 'Tier sandbox', 'Weights are renormalized to sum to 1.0',
      [out.node, scale.node, h('div', { class: 'gki-derived' }, [dNorm.node])],
      [
        groupLabel('Input scores'), adoption.row, agentic.row, output.row,
        groupLabel('Weights (defaults 0.5 / 0.2 / 0.3)'), wA.row, wG.row, wO.row
      ],
      'Renormalizing is why raising one weight lowers the others’ influence even though you did not touch them.');

    update();
  }

  /* =========================================================
     INSTRUMENT: Cursor Boost
     Final (pre-maturity) = min(Primary + boost x Cursor, 100)
     ========================================================= */
  function instCursorBoost(mount) {
    var out = readout('pre-maturity');
    var dRaw = derivedRow('Primary + boost, before the cap');
    var dGain = derivedRow('What the Cursor boost actually added');

    var primary = slider('Primary score', { min: 0, max: 100, step: 1, value: 70 });
    var cursor = slider('Cursor score', { min: 0, max: 100, step: 1, value: 60 });
    var boost = slider('Secondary boost', { min: 0, max: 1, step: 0.05, value: 0.25 });

    function update() {
      var raw = primary.value() + boost.value() * cursor.value();
      var capped = Math.min(raw, 100);
      var gain = capped - primary.value();

      out.set(capped.toFixed(0));
      out.setBand(null);
      dRaw.set(raw.toFixed(1));
      dGain.set('+' + gain.toFixed(1));

      primary.setLabel(String(primary.value()));
      cursor.setLabel(String(cursor.value()));
      boost.setLabel(boost.value().toFixed(2));
    }

    [primary, cursor, boost].forEach(function (s) {
      s.input.addEventListener('input', update);
    });

    sandbox(mount, 'Boost sandbox', 'Watch the cap absorb the boost as Primary rises',
      [out.node, h('div', { class: 'gki-derived' }, [dRaw.node, dGain.node])],
      [groupLabel('Inputs'), primary.row, cursor.row, groupLabel('Setting'), boost.row],
      'SCORE_SECONDARY_BOOST defaults to 0.25. Push Primary above 80 and most of the boost disappears into the min(..., 100) cap.');

    update();
  }

  /* =========================================================
     INSTRUMENT: Output Score
     = SUM(PR effort) + DCWeight x SUM(DC effort)
                      + ReviewWeight x SUM(reviewed-PR effort)
     ========================================================= */
  function instOutputScore(mount) {
    var out = readout('points');
    var dPr = derivedRow('From PRs');
    var dDc = derivedRow('From direct commits');
    var dRv = derivedRow('From reviews');

    var pr = slider('PR effort total', { min: 0, max: 200, step: 1, value: 90 });
    var dc = slider('Direct commit effort', { min: 0, max: 200, step: 1, value: 40 });
    var rv = slider('Reviewed-PR effort', { min: 0, max: 200, step: 1, value: 60 });
    var dcW = slider('Direct Commit Weight', { min: 0, max: 1, step: 0.05, value: 0.5 });
    var rvW = slider('Review Weight', { min: 0, max: 1, step: 0.05, value: 0.5 });

    function update() {
      var fromPr = pr.value();
      var fromDc = dcW.value() * dc.value();
      var fromRv = rvW.value() * rv.value();
      var total = fromPr + fromDc + fromRv;

      out.set(total.toFixed(0));
      out.setBand(null);
      dPr.set(fromPr.toFixed(0));
      dDc.set(fromDc.toFixed(0));
      dRv.set(fromRv.toFixed(0));

      [pr, dc, rv].forEach(function (s) { s.setLabel(String(s.value())); });
      [dcW, rvW].forEach(function (s) { s.setLabel(s.value().toFixed(2)); });
    }

    [pr, dc, rv, dcW, rvW].forEach(function (s) {
      s.input.addEventListener('input', update);
    });

    sandbox(mount, 'Output sandbox', 'Three effort pools, two weights',
      [out.node, h('div', { class: 'gki-derived' }, [dPr.node, dDc.node, dRv.node])],
      [
        groupLabel('Effort pools'), pr.row, dc.row, rv.row,
        groupLabel('Weights'), dcW.row, rvW.row
      ],
      'Effort values are illustrative. The weights are the real settings; both default to 0.5.');

    update();
  }

  /* =========================================================
     INSTRUMENT: Direct Commits
     contribution = DCWeight x SUM(DC effort)
     ========================================================= */
  function instDirectCommits(mount) {
    var out = readout('points');
    var dShare = derivedRow('Share of a 200-point Output Score');

    var effort = slider('Direct commit effort', { min: 0, max: 300, step: 1, value: 80 });
    var weight = slider('Direct Commit Weight', { min: 0, max: 1, step: 0.05, value: 0.5 });

    function update() {
      var contribution = weight.value() * effort.value();
      out.set(contribution.toFixed(0));
      out.setBand(null);
      dShare.set((contribution / 200 * 100).toFixed(0) + '%');
      effort.setLabel(String(effort.value()));
      weight.setLabel(weight.value().toFixed(2));
    }

    [effort, weight].forEach(function (s) {
      s.input.addEventListener('input', update);
    });

    sandbox(mount, 'Contribution sandbox', 'Weight 0 removes direct commits entirely',
      [out.node, h('div', { class: 'gki-derived' }, [dShare.node])],
      [groupLabel('Inputs'), effort.row, groupLabel('Setting'), weight.row],
      'Direct Commit Weight defaults to 0.5. Effort per commit is COALESCE(effort_score, auto_effort_score, 0).');

    update();
  }

  /* =========================================================
     INSTRUMENT: Productivity Uplift
     VolumeSpeedupFactor = Treatment / Control
     HoursSaved = (factor - 1) x 40
     Value = HoursSaved x rate x devs
     ========================================================= */
  function instUplift(mount) {
    var out = readout('per year');
    var dFactor = derivedRow('Volume speedup factor');
    var dHours = derivedRow('Hours saved per dev per week');
    var dWeek = derivedRow('Value per week');

    var treat = slider('Treatment changes / dev / week', { min: 0.5, max: 20, step: 0.1, value: 7.5 });
    var ctrl = slider('Control changes / dev / week', { min: 0.5, max: 20, step: 0.1, value: 6 });
    var devs = slider('Developers', { min: 1, max: 500, step: 1, value: 80 });
    var rate = slider('Hourly rate (USD)', { min: 20, max: 250, step: 5, value: 85 });

    function money(n) {
      return '$' + Math.round(n).toLocaleString('en-US');
    }

    function update() {
      var factor = ctrl.value() > 0 ? treat.value() / ctrl.value() : 0;
      var hours = (factor - 1) * 40;
      var perWeek = hours * rate.value() * devs.value();
      var perYear = perWeek * 52;

      out.set(money(perYear));
      out.setBand(null);
      dFactor.set(factor.toFixed(2) + '×');
      dHours.set(hours.toFixed(1));
      dWeek.set(money(perWeek));

      treat.setLabel(treat.value().toFixed(1));
      ctrl.setLabel(ctrl.value().toFixed(1));
      devs.setLabel(String(devs.value()));
      rate.setLabel('$' + rate.value());
    }

    [treat, ctrl, devs, rate].forEach(function (s) {
      s.input.addEventListener('input', update);
    });

    sandbox(mount, 'Value sandbox', 'Estimated value of the delivery difference',
      [out.node, h('div', { class: 'gki-derived' }, [dFactor.node, dHours.node, dWeek.node])],
      [
        groupLabel('Delivery rates'), treat.row, ctrl.row,
        groupLabel('Your organization'), devs.row, rate.row
      ],
      'An estimate built from the documented formula, not a measurement. It assumes a 40-hour week and that the volume difference between cohorts is attributable to AI.');

    update();
  }

  /* =========================================================
     INSTRUMENT: Cycle Time — proportion, not calculation
     Cycle Time = Coding + Pickup + Review + Deploy
     ========================================================= */
  function instCycleTime(mount) {
    var STAGES = [
      { key: 'Coding', color: '--gki-tier-regular', value: 18 },
      { key: 'Pickup', color: '--gki-tier-explorer', value: 9 },
      { key: 'Review', color: '--gki-tier-emerging', value: 22 },
      { key: 'Deploy', color: '--gki-tier-power', value: 6 }
    ];

    var out = readout('hours total');
    var bar = h('div', { class: 'gki-propbar' });
    var legend = h('div', { class: 'gki-proplegend' });

    var segs = [];
    var legends = [];
    var sliders = [];

    STAGES.forEach(function (stage) {
      var seg = h('span');
      seg.style.background = 'var(' + stage.color + ')';
      bar.appendChild(seg);
      segs.push(seg);

      var b = h('b');
      var swatch = h('i');
      swatch.style.background = 'var(' + stage.color + ')';
      legend.appendChild(h('span', {}, [swatch, document.createTextNode(stage.key + ' '), b]));
      legends.push(b);

      var s = slider(stage.key + ' (hours)', { min: 0, max: 72, step: 1, value: stage.value });
      sliders.push(s);
    });

    function update() {
      var values = sliders.map(function (s) { return s.value(); });
      var total = values.reduce(function (a, b) { return a + b; }, 0);

      out.set(total.toFixed(0));
      out.setBand(null);

      values.forEach(function (v, i) {
        var pct = total > 0 ? (v / total * 100) : 0;
        segs[i].style.flexGrow = String(Math.max(v, 0.0001));
        segs[i].textContent = pct >= 12 ? Math.round(pct) + '%' : '';
        legends[i].textContent = v + 'h · ' + Math.round(pct) + '%';
        sliders[i].setLabel(v + 'h');
      });
    }

    sliders.forEach(function (s) { s.input.addEventListener('input', update); });

    sandbox(mount, 'Where the time goes', 'The total matters less than the shape',
      [out.node, bar, legend],
      [groupLabel('Stage durations')].concat(sliders.map(function (s) { return s.row; })),
      'Stage durations are illustrative. Deploy is only measured when release detection is configured; without it, Cycle Time ends at merge.');

    update();
  }

  /* =========================================================
     INSTRUMENT: Lead Time — sequence, not calculation
     ========================================================= */
  function instLeadTime(mount) {
    mount.appendChild(instrumentHead('Which release counts', 'The subtraction is trivial; the matching is not'));
    mount.appendChild(h('ul', { class: 'gki-timeline' }, [
      h('li', {}, [
        h('span', { class: 'gki-tl-dot', text: '1' }),
        h('div', { class: 'gki-tl-body' }, [
          h('strong', { text: 'first_commit_at' }),
          h('span', { text: 'The earliest commit on the branch that became this PR. This is the clock start, not the PR open time.' })
        ])
      ]),
      h('li', {}, [
        h('span', { class: 'gki-tl-dot', text: '2' }),
        h('div', { class: 'gki-tl-body' }, [
          h('strong', { text: 'merged_at' }),
          h('span', { text: 'The merge itself does not stop the clock. It only sets the earliest release that is allowed to match.' })
        ])
      ]),
      h('li', {}, [
        h('span', { class: 'gki-tl-dot gki-tl-dot--rule', text: '✓' }),
        h('div', { class: 'gki-tl-body' }, [
          h('strong', { text: 'The matching rule' }),
          h('span', { text: 'The earliest non-prerelease release on the same repo whose released_at is at or after merged_at, with branch- and SHA-aware matching. A prerelease or a release on another repo is skipped.' })
        ])
      ]),
      h('li', {}, [
        h('span', { class: 'gki-tl-dot', text: '3' }),
        h('div', { class: 'gki-tl-body' }, [
          h('strong', { text: 'released_at' }),
          h('span', {}, [
            document.createTextNode('Clock stop. '),
            h('code', { text: 'Lead Time = released_at − first_commit_at' }),
            document.createTextNode(', clamped at 0.')
          ])
        ])
      ])
    ]));
    mount.appendChild(sourceNote('A PR with no matching release has no Lead Time and is excluded rather than counted as zero. This is why Lead Time coverage drops on repos without release detection.'));
  }

  /* =========================================================
     INSTRUMENT: AI-Assisted % — attribution, not calculation
     ========================================================= */
  function instAiAssisted(mount) {
    mount.appendChild(instrumentHead('How a change is attributed', 'Either path is sufficient on its own'));
    mount.appendChild(h('ul', { class: 'gki-timeline' }, [
      h('li', {}, [
        h('span', { class: 'gki-tl-dot gki-tl-dot--rule', text: 'A' }),
        h('div', { class: 'gki-tl-body' }, [
          h('strong', { text: 'An AI co-author trailer is present' }),
          h('span', { text: 'The change carries an explicit co-author trailer from an AI tool. Deterministic — no timing involved.' })
        ])
      ]),
      h('li', {}, [
        h('span', { class: 'gki-tl-dot gki-tl-dot--rule', text: 'B' }),
        h('div', { class: 'gki-tl-body' }, [
          h('strong', { text: 'Or the developer had AI events nearby' }),
          h('span', {}, [
            document.createTextNode('AI activity falls inside the correlation window of the change’s lifecycle — '),
            h('code', { text: 'default 60 minutes' }),
            document.createTextNode('. Inferred, so a wider window attributes more.')
          ])
        ])
      ]),
      h('li', {}, [
        h('span', { class: 'gki-tl-dot', text: '=' }),
        h('div', { class: 'gki-tl-body' }, [
          h('strong', { text: 'Lines, not changes' }),
          h('span', { text: 'The percentage is lines changed in attributed PRs and commits over total lines changed — so one large attributed PR moves it more than several small ones.' })
        ])
      ])
    ]));
    mount.appendChild(sourceNote('Path B is a correlation, not a causal claim. Widening the window raises the number without any change in behaviour.'));
  }

  /* =========================================================
     INSTRUMENT: CapEx / OpEx — precedence, not calculation
     ========================================================= */
  function instCapexOpex(mount) {
    mount.appendChild(instrumentHead('Which value wins', 'First non-null wins'));

    var rows = [
      ['1', 'capex_opex', 'A manual override stored on the PR or commit record. Always wins when set.'],
      ['2', 'capex_opex_auto', 'Automatic classification from the PR or commit category. Used only when no override exists.'],
      ['3', "'Uncategorized'", 'Fallback when neither is set. Aggregates as OpEx for reporting, so unclassified work quietly counts as operating expense.']
    ];

    var table = h('div', { class: 'gki-precedence' });
    rows.forEach(function (r) {
      table.appendChild(h('div', { class: 'gki-prec-row' }, [
        h('span', { class: 'gki-prec-rank', text: r[0] }),
        h('code', { text: r[1] }),
        h('p', { text: r[2] })
      ]));
    });
    mount.appendChild(table);
    mount.appendChild(sourceNote('Aggregated as effort-weighted hours per developer per month, split CapEx / OpEx. The third row is the one worth watching — it is a silent default, not a decision.'));
  }

  var INSTRUMENTS = {
    maturity: instMaturity,
    adoption: instAdoption,
    aitier: instAiTier,
    cursorboost: instCursorBoost,
    outputscore: instOutputScore,
    directcommits: instDirectCommits,
    uplift: instUplift,
    cycletime: instCycleTime,
    leadtime: instLeadTime,
    aiassisted: instAiAssisted,
    capexopex: instCapexOpex
  };

  function initInstruments() {
    var mounts = document.querySelectorAll('.gki-instrument[data-instrument]');
    for (var i = 0; i < mounts.length; i++) {
      var kind = mounts[i].getAttribute('data-instrument');
      var build = INSTRUMENTS[kind];
      if (!build) continue;
      try {
        build(mounts[i]);
      } catch (e) {
        // An instrument must never take the page down with it.
        mounts[i].parentNode.removeChild(mounts[i]);
        if (window.console && console.warn) {
          console.warn('[gki] instrument "' + kind + '" failed to build', e);
        }
      }
    }
  }

  /* =========================================================
     COMMAND PALETTE
     ========================================================= */
  var palette = {
    root: null, input: null, results: null,
    pages: [], matches: [], active: 0, lastFocus: null
  };

  var SEARCH_SVG = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" ' +
    'stroke-width="2" stroke-linecap="round" aria-hidden="true">' +
    '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>';

  function isMac() {
    return /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent || '');
  }

  function rank(query, text) {
    var q = query.toLowerCase();
    var s = (text || '').toLowerCase();
    var direct = s.indexOf(q);
    if (direct === 0) return 1000;
    if (direct > 0) return 600 - direct;
    // Subsequence, so "adsc" still finds "Agent Adoption Score".
    var qi = 0;
    for (var i = 0; i < s.length && qi < q.length; i++) {
      if (s.charAt(i) === q.charAt(qi)) qi++;
    }
    return qi === q.length ? 200 - s.length * 0.1 : -1;
  }

  function renderResults() {
    var q = palette.input.value.trim();
    var grouped = !q;

    if (grouped) {
      palette.matches = palette.pages.slice();
    } else {
      palette.matches = palette.pages
        .map(function (p) {
          var r = Math.max(rank(q, p.title), rank(q, p.desc) - 200);
          return { p: p, r: r };
        })
        .filter(function (x) { return x.r >= 0; })
        .sort(function (a, b) { return b.r - a.r; })
        .map(function (x) { return x.p; });
    }

    palette.results.innerHTML = '';

    if (!palette.matches.length) {
      palette.results.appendChild(h('li', {
        class: 'gki-palette-empty',
        text: 'No pages match “' + q + '”'
      }));
      return;
    }

    /* When there is no query the list is grouped by section. The search
       index is not guaranteed to arrive sorted, so group into buckets in
       first-seen order rather than emitting a header whenever the value
       changes — otherwise the same section heading repeats several times
       down the list. */
    if (grouped) {
      var order = [];
      var buckets = {};
      palette.matches.forEach(function (p) {
        var key = p.cat || 'other';
        if (!buckets[key]) { buckets[key] = []; order.push(key); }
        buckets[key].push(p);
      });
      var flat = [];
      order.forEach(function (key) {
        buckets[key].forEach(function (p) { flat.push(p); });
      });
      palette.matches = flat;
    }

    var lastGroup = null;
    palette.matches.forEach(function (p, i) {
      if (grouped && p.cat !== lastGroup) {
        palette.results.appendChild(h('li', {
          class: 'gki-palette-group',
          text: (p.cat || 'Other').replace(/-/g, ' ')
        }));
        lastGroup = p.cat;
      }

      var li = h('li', {
        class: 'gki-palette-item',
        'data-active': String(i === palette.active),
        'data-index': String(i),
        role: 'option'
      }, [
        h('span', { class: 'gki-pi-title', text: p.title }),
        h('span', { class: 'gki-pi-cat', text: (p.cat || '').replace(/-/g, ' ') })
      ]);

      li.addEventListener('mousemove', function () {
        if (palette.active !== i) { palette.active = i; paintResults(); }
      });
      li.addEventListener('click', choosePalette);

      palette.results.appendChild(li);
    });
  }

  function paintResults() {
    var items = palette.results.querySelectorAll('.gki-palette-item');
    for (var i = 0; i < items.length; i++) {
      items[i].setAttribute('data-active', String(+items[i].getAttribute('data-index') === palette.active));
    }
    var cur = palette.results.querySelector('.gki-palette-item[data-active="true"]');
    if (cur && cur.scrollIntoView) cur.scrollIntoView({ block: 'nearest' });
  }

  function choosePalette() {
    var p = palette.matches[palette.active];
    if (!p || !p.url) return;
    window.location.href = p.url;
  }

  function openPalette() {
    if (!palette.root) return;
    palette.lastFocus = document.activeElement;
    palette.root.hidden = false;
    palette.input.value = '';
    palette.active = 0;
    renderResults();
    palette.input.focus();
  }

  function closePalette() {
    if (!palette.root) return;
    palette.root.hidden = true;
    if (palette.lastFocus && palette.lastFocus.focus) palette.lastFocus.focus();
  }

  function buildPaletteTrigger() {
    var hint = isMac() ? '⌘K' : 'Ctrl K';
    var btn = h('button', {
      type: 'button',
      class: 'gki-palette-trigger',
      'aria-label': 'Search documentation'
    });
    btn.innerHTML = SEARCH_SVG;
    btn.appendChild(h('span', { text: 'Search docs' }));
    btn.appendChild(h('span', { class: 'gki-kbd', text: hint }));
    btn.addEventListener('click', openPalette);
    return btn;
  }

  function initPalette() {
    var data = window.gkiSearchData;
    if (!data || !data.pages || !data.pages.length) return;
    palette.pages = data.pages;

    /* Replace the visible search affordance.

       The trigger goes in the left nav, once, so search sits in the same
       place on every page — including Home, which has no search box of
       its own. The original inputs stay in the DOM, hidden, so the legacy
       search module keeps its bindings and nothing breaks if this file is
       ever removed.

       Falls back to replacing each search box in place if the nav header
       is not present (the auth gate template, for instance). */
    var searches = document.querySelectorAll('.gki-site-search');
    for (var i = 0; i < searches.length; i++) {
      searches[i].style.display = 'none';
    }

    var navHeader = document.querySelector('.gki-nav .gki-nav-header');
    if (navHeader && navHeader.parentNode) {
      var slot = h('div', { class: 'gki-palette-slot' }, [buildPaletteTrigger()]);
      if (navHeader.nextSibling) {
        navHeader.parentNode.insertBefore(slot, navHeader.nextSibling);
      } else {
        navHeader.parentNode.appendChild(slot);
      }
    } else if (searches.length) {
      searches[0].parentNode.insertBefore(buildPaletteTrigger(), searches[0]);
    }

    var hint = isMac() ? '⌘K' : 'Ctrl K';

    palette.input = h('input', {
      type: 'text',
      class: 'gki-palette-input',
      id: 'gki-palette-input',
      placeholder: 'Search metrics, playbooks, settings…',
      autocomplete: 'off',
      spellcheck: 'false',
      'aria-label': 'Search documentation'
    });
    palette.results = h('ul', { class: 'gki-palette-results', role: 'listbox' });

    var field = h('div', { class: 'gki-palette-field' });
    field.innerHTML = SEARCH_SVG;
    field.appendChild(palette.input);

    var foot = h('div', { class: 'gki-palette-foot' }, [
      h('span', {}, [h('span', { class: 'gki-kbd', text: '↑↓' }), document.createTextNode(' navigate')]),
      h('span', {}, [h('span', { class: 'gki-kbd', text: '↵' }), document.createTextNode(' open')]),
      h('span', {}, [h('span', { class: 'gki-kbd', text: 'esc' }), document.createTextNode(' close')]),
      h('span', {}, [h('span', { class: 'gki-kbd', text: hint }), document.createTextNode(' anywhere')])
    ]);

    palette.root = h('div', { class: 'gki-palette-backdrop', hidden: 'hidden' }, [
      h('div', { class: 'gki-palette', role: 'dialog', 'aria-modal': 'true', 'aria-label': 'Search documentation' }, [
        field, palette.results, foot
      ])
    ]);

    document.body.appendChild(palette.root);

    palette.input.addEventListener('input', function () {
      palette.active = 0;
      renderResults();
    });

    palette.root.addEventListener('mousedown', function (e) {
      if (e.target === palette.root) closePalette();
    });

    document.addEventListener('keydown', function (e) {
      var key = e.key || '';
      if ((e.metaKey || e.ctrlKey) && key.toLowerCase() === 'k') {
        e.preventDefault();
        if (palette.root.hidden) openPalette(); else closePalette();
        return;
      }
      // "/" opens search, unless the user is already typing somewhere.
      if (key === '/' && palette.root.hidden) {
        var t = e.target;
        var tag = t && t.tagName ? t.tagName.toLowerCase() : '';
        if (tag !== 'input' && tag !== 'textarea' && !(t && t.isContentEditable)) {
          e.preventDefault();
          openPalette();
        }
        return;
      }
      if (palette.root.hidden) return;

      if (key === 'Escape') { e.preventDefault(); closePalette(); }
      else if (key === 'ArrowDown') { e.preventDefault(); palette.active = Math.min(palette.active + 1, palette.matches.length - 1); paintResults(); }
      else if (key === 'ArrowUp') { e.preventDefault(); palette.active = Math.max(palette.active - 1, 0); paintResults(); }
      else if (key === 'Enter') { e.preventDefault(); choosePalette(); }
    });
  }

  /* =========================================================
     ENTRY REVEAL
     ========================================================= */
  function initReveal() {
    if (!('IntersectionObserver' in window)) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var targets = document.querySelectorAll(
      '.gki-card-grid > .gki-card, .gki-section, .gki-related, .gki-below-cards, .gki-family'
    );
    if (!targets.length) return;

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var el = entry.target;
        var delay = parseInt(el.getAttribute('data-reveal-delay'), 10) || 0;

        window.setTimeout(function () {
          el.classList.add('gki-revealed');
          window.setTimeout(function () { el.classList.add('gki-reveal-done'); }, 400);
        }, delay);

        observer.unobserve(el);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });

    for (var i = 0; i < targets.length; i++) {
      var el = targets[i];
      el.classList.add('gki-reveal');

      if (el.classList.contains('gki-card')) {
        var idx = Array.prototype.indexOf.call(el.parentNode.children, el);
        el.setAttribute('data-reveal-delay', String((idx % 3) * 40));
      }

      // Anything already on screen reveals at once rather than waiting
      // for a scroll that may never come.
      var rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight && rect.bottom > 0) {
        el.classList.add('gki-revealed', 'gki-reveal-done');
        continue;
      }
      observer.observe(el);
    }
  }

  /* ---------- boot ---------- */
  function init() {
    try { initPalette(); } catch (e) {}
    try { initInstruments(); } catch (e) {}
    try { initReveal(); } catch (e) {}
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
