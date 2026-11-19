/* Core Gas admin field UI: colour pickers, media picker, repeaters, conditional meta boxes. */
(function ($) {
  'use strict';
  var cfg = window.coregasFields || { conditions: {}, chooseText: 'Use this image' };

  function initColors(ctx) {
    $(ctx).find('.cg-color').each(function () {
      if (!$(this).closest('.wp-picker-container').length) $(this).wpColorPicker();
    });
  }

  /* media picker */
  $(document).on('click', '.cg-image__pick', function (e) {
    e.preventDefault();
    var wrap = $(this).closest('[data-cg-image]');
    var frame = wp.media({ title: $(this).text(), button: { text: cfg.chooseText }, library: { type: 'image' }, multiple: false });
    frame.on('select', function () {
      var a = frame.state().get('selection').first().toJSON();
      var url = (a.sizes && a.sizes.medium) ? a.sizes.medium.url : a.url;
      wrap.find('input[type=hidden]').val(a.id).trigger('change');
      wrap.find('.cg-image__preview').html($('<img alt="">').attr('src', url));
    });
    frame.open();
  });
  $(document).on('click', '.cg-image__clear', function (e) {
    e.preventDefault();
    var wrap = $(this).closest('[data-cg-image]');
    wrap.find('input[type=hidden]').val('');
    wrap.find('.cg-image__preview').empty();
  });

  /* repeaters */
  function reindex(rep) {
    var name = rep.data('name'), id = rep.data('id');
    rep.children('.cg-repeater__rows').children('.cg-repeater__row').each(function (i) {
      $(this).find('[name]').each(function () {
        var n = $(this).attr('name');
        var rest = n.substring(name.length).replace(/^\[[^\]]*\]/, '[' + i + ']');
        $(this).attr('name', name + rest);
      });
      $(this).find('[id]').each(function () {
        var v = $(this).attr('id');
        if (v.indexOf(id + '-') === 0) $(this).attr('id', v.replace(new RegExp('^' + id + '-[^-]+'), id + '-' + i));
      });
    });
  }
  $(document).on('click', '.cg-repeater__add', function (e) {
    e.preventDefault();
    var rep = $(this).closest('[data-cg-repeater]');
    var rows = rep.children('.cg-repeater__rows');
    var html = rep.children('.cg-repeater__tpl').html().replace(/__i__/g, rows.children().length);
    var row = $(html);
    rows.append(row);
    initColors(row);
    reindex(rep);
  });
  $(document).on('click', '.cg-repeater__remove', function (e) {
    e.preventDefault();
    var rep = $(this).closest('[data-cg-repeater]');
    $(this).closest('.cg-repeater__row').remove();
    reindex(rep);
  });
  $(document).on('click', '.cg-repeater__up, .cg-repeater__down', function (e) {
    e.preventDefault();
    var row = $(this).closest('.cg-repeater__row');
    if ($(this).hasClass('cg-repeater__up')) row.prev('.cg-repeater__row').before(row);
    else row.next('.cg-repeater__row').after(row);
    reindex($(this).closest('[data-cg-repeater]'));
  });

  /* show only the meta boxes relevant to the chosen section type */
  function applyConditions() {
    $.each(cfg.conditions, function (boxId, rule) {
      var show = true;
      $.each(rule, function (key, values) {
        var val = $('#cg-' + key).val();
        if (values.indexOf(val) === -1) show = false;
      });
      $('#' + boxId).toggle(show);
      $('#' + boxId + '-hide').closest('label').toggle(show);
    });
  }

  /* ---------- page editor: section panels ---------- */
  function applyPanelType(panel) {
    var type = panel.find('.cgs-type').val();
    panel.find('.cgs-group').each(function () {
      var types = String($(this).data('types')).split(',');
      $(this).toggle(types.indexOf('all') !== -1 || types.indexOf(type) !== -1);
    });
    var label = panel.find('.cgs-type option:selected').text();
    panel.find('.cgs-panel__type').text(label);
  }
  function renumber() {
    // Visual order is CSS `order` (moving DOM would break the text editors); the hidden input carries it.
    var panels = $('.cgs-list > .cgs-panel').get().sort(function (a, b) {
      return (+$(a).find('.cgs-order').val()) - (+$(b).find('.cgs-order').val());
    });
    $.each(panels, function (i, p) { $(p).css('order', i).find('.cgs-order').val(i); });
  }
  $(document).on('click', '.cgs-up, .cgs-down', function (e) {
    e.preventDefault(); e.stopPropagation();
    var panel = $(this).closest('.cgs-panel');
    var cur = +panel.find('.cgs-order').val();
    var target = cur + ($(this).hasClass('cgs-up') ? -1 : 1);
    var other = $('.cgs-list > .cgs-panel').filter(function () { return +$(this).find('.cgs-order').val() === target; });
    if (!other.length) return;
    other.find('.cgs-order').val(cur);
    panel.find('.cgs-order').val(target);
    renumber();
  });
  $(document).on('change', '.cgs-type', function () { applyPanelType($(this).closest('.cgs-panel')); });
  $(document).on('change', '.cgs-remove input', function () { $(this).closest('.cgs-panel').toggleClass('is-removing', this.checked); });
  $(document).on('input', '.cgs-basics input[name$="[title]"]', function () { $(this).closest('.cgs-panel').find('.cgs-panel__title').text(this.value); });
  // Text editors inside a panel that was closed when the page loaded need a nudge to size themselves.
  $(document).on('toggle', '.cgs-panel', function () {
    if (this.open) { window.dispatchEvent(new Event('resize')); }
  });
  document.addEventListener('toggle', function (e) {
    if (e.target.classList && e.target.classList.contains('cgs-panel') && e.target.open) { window.dispatchEvent(new Event('resize')); }
  }, true);
  // The "Add section" button saves the page like Update does.
  $(document).on('click', 'button[name="cgs_add_submit"]', function (e) {
    if (!$('#cgs-add-type').val()) { e.preventDefault(); $('#cgs-add-type').focus(); }
  });

  /* ---------- Theme Settings: one tab at a time ---------- */
  function initTabs() {
    var tabs = cfg.tabs || {};
    var names = [];
    $.each(tabs, function (boxId, tab) { if (names.indexOf(tab) === -1) names.push(tab); });
    if (!names.length) return;
    var bar = $('<div class="cg-tabs" role="tablist"></div>');
    $.each(names, function (i, name) {
      $('<button type="button" role="tab"></button>').text(name).attr('data-tab', name).appendTo(bar);
    });
    $('#post-body-content').append(bar).append('<p class="cg-tabs-help">' + (cfg.tabsHelp || '') + '</p>');
    function show(name) {
      bar.find('button').removeClass('is-active').attr('aria-selected', 'false').filter(function () { return $(this).attr('data-tab') === name; }).addClass('is-active').attr('aria-selected', 'true');
      $.each(tabs, function (boxId, tab) { $('#' + boxId).toggle(tab === name); });
      try { sessionStorage.setItem('cgTab', name); } catch (e) {}
    }
    bar.on('click', 'button', function () { show($(this).attr('data-tab')); });
    var start = null;
    try { start = sessionStorage.getItem('cgTab'); } catch (e) {}
    show(names.indexOf(start) !== -1 ? start : names[0]);
  }

  $(function () {
    $('.cgs-panel').each(function () { applyPanelType($(this)); });
    initTabs();
    initColors(document.body);
    // Only real rows, not the hidden templates.
    $('.cg-repeater__tpl').each(function () { /* templates stay inert */ });
    applyConditions();
    $(document).on('change', '#cg-section_type', applyConditions);
  });
})(jQuery);
