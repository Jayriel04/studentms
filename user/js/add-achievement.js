(function ($) {
  'use strict';

  function debounce(fn, wait) {
    var t;
    return function () {
      var ctx = this, args = arguments;
      clearTimeout(t);
      t = setTimeout(function () { fn.apply(ctx, args); }, wait);
    };
  }

  function escapeHtml(text) {
    return $('<div>').text(text).html();
  }

  var allSuggestions = [];
  $('#skillSuggestionsList .skill-item').each(function () {
    allSuggestions.push({
      id: $(this).data('id'),
      name: $(this).data('name'),
      category: $(this).data('category')
    });
  });

  var pageSize = 10, currentPage = 1, currentQuery = '';

  function renderSuggestions(resetPage) {
    if (resetPage) currentPage = 1;
    var filtered = allSuggestions.filter(function (s) {
      if (!currentQuery) return true;
      return s.name.toLowerCase().indexOf(currentQuery) !== -1;
    });
    var start = (currentPage - 1) * pageSize;
    var page = filtered.slice(start, start + pageSize);
    var $list = $('#skillSuggestionsList').empty();
    if (page.length === 0) {
      $list.append('<div class="text-muted">No suggestions.</div>');
    } else {
      page.forEach(function (s) {
        var $item = $('<div class="skill-item" data-name="' + escapeHtml(s.name) + '" data-id="' + s.id + '" data-category="' + escapeHtml(s.category) + '"></div>');
        var $btn = $('<button type="button" class="btn btn-sm btn-outline-success skill-sugg">' + escapeHtml(s.name) + ' <small class="text-muted">(' + escapeHtml(s.category) + ')</small></button>');
        $item.append($btn);
        $list.append($item);
      });
    }
    var hasMore = filtered.length > start + pageSize;
    $('#loadMoreTags').toggle(hasMore);
    $('#loadBackTags').toggle(!hasMore && currentPage > 1);
  }

  $('#skillSearch').on('input', debounce(function () {
    currentQuery = $(this).val().toLowerCase().trim();
    renderSuggestions(true);
  }, 250));

  $('#clearSkillSearch').on('click', function () {
    $('#skillSearch').val('');
    currentQuery = '';
    renderSuggestions(true);
  });

  $('#loadBackTags').on('click', function () {
    currentPage = 1;
    renderSuggestions(true);
  });

  $('#loadMoreTags').on('click', function () {
    currentPage++;
    renderSuggestions(false);
  });

  $('#skillSuggestionsList').on('click', '.skill-sugg', function () {
    var $item = $(this).closest('.skill-item');
    var name = $item.data('name');
    var category = $item.data('category');
    var id = $item.data('id');
    selectTag(name, id, category);
  });

  function selectTag(name, id, category) {
    $('#skillsContainer').empty();
    var $chip = $(
      '<span class="badge badge-pill badge-success mr-2">' + escapeHtml(name) +
      ' <a href="#" class="text-white ml-1 remove-skill" style="text-decoration:none;">&times;</a></span>'
    );
    $('#skillsContainer').append($chip);
    $('#skillsHidden').val(name);
    if (typeof id !== 'undefined') $('#skillsIdHidden').val(id);
    else $('#skillsIdHidden').val('');
    if (typeof category !== 'undefined' && category) {
      $('#ach_category').val(category);
    }
  }

  $('#skillsContainer').on('click', '.remove-skill', function (e) {
    e.preventDefault();
    $('#skillsContainer').empty();
    $('#skillsHidden').val('');
  });

  window.prepareSkills = function () {
    /* hidden input already set by selectTag */
  };

  // Add tag via AJAX
  $('#saveTagBtn').on('click', function (e) {
    e.preventDefault();
    var $btn = $(this);
    var name = $('#tagName').val().trim();
    var category = $('#tagCategory').val();
    if (!name) {
      alert('Please enter a tag name');
      return;
    }
    $btn.prop('disabled', true);
    $.post(window.location.href, {
      add_tag_ajax: 1,
      tag_name: name,
      tag_category: category
    }, function (res) {
      $btn.prop('disabled', false);
      if (res && res.success) {
        allSuggestions.unshift({
          id: res.tblskill_id || res.skill_id || res.id,
          name: res.name,
          category: res.category
        });
        if (typeof $().modal === 'function') {
          $('#addTagModal').modal('hide');
        } else {
          $('#addTagModal').hide();
        }
        $('#tagName').val('');
        $('#tagCategory').val('Non-Academic');
        renderSuggestions(true);
        selectTag(res.name, res.tblskill_id || res.skill_id || res.id);
      } else {
        alert((res && res.msg) ? res.msg : 'Error adding tag');
      }
    }, 'json').fail(function () {
      $btn.prop('disabled', false);
      alert('Request failed');
    });
  });

  // Initial render
  renderSuggestions(true);
})(jQuery);