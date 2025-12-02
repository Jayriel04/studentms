(function ($) {
  'use strict';

  // --- DOM Elements ---
  let selectedTag = null;
  const searchInput = document.getElementById('searchInput');
  const tagsContainer = document.getElementById('tagsContainer');
  const fileInput = document.getElementById('fileInput');
  const filePreview = document.getElementById('filePreview');
  const fileNameEl = document.getElementById('fileName');
  const fileSizeEl = document.getElementById('fileSize');
  const removeFileBtn = document.getElementById('removeFileBtn');
  const skillsHiddenInput = document.getElementById('skillsHidden');
  const skillsIdHiddenInput = document.getElementById('skillsIdHidden');
  const achCategorySelect = document.getElementById('ach_category');
  const loadMoreBtn = document.getElementById('loadMoreTags');
  const showLessBtn = document.getElementById('showLessTags');

  // --- Tag Selection & Filtering ---
  $(tagsContainer).on('click', '.tag-chip', function() {
    $('.tag-chip').removeClass('selected');
    $(this).addClass('selected');
    selectedTag = this;
    const name = $(this).data('name');
    const id = $(this).data('id');
    const category = $(this).data('category');
    
    if (skillsHiddenInput) skillsHiddenInput.value = name;
    if (skillsIdHiddenInput) skillsIdHiddenInput.value = id;
    if (achCategorySelect) achCategorySelect.value = category;
  });

  // --- Tag Pagination Logic ---
  const allTags = Array.from(tagsContainer.children);
  const pageSize = 10;
  let currentPage = 1;

  function renderTags() {
    const searchValue = $(searchInput).val().toLowerCase();
    const filteredTags = allTags.filter(tag => tag.textContent.toLowerCase().includes(searchValue));

    allTags.forEach(tag => $(tag).hide());

    const tagsToShow = filteredTags.slice(0, currentPage * pageSize);
    tagsToShow.forEach(tag => $(tag).show());

    $(loadMoreBtn).toggle(tagsToShow.length < filteredTags.length);
    $(showLessBtn).toggle(currentPage > 1);
  }

  if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', function() {
      currentPage++;
      renderTags();
    });
  }

  if (showLessBtn) {
    showLessBtn.addEventListener('click', function() {
      currentPage = 1;
      renderTags();
    });
  }

  $(searchInput).on('input', function() {
    currentPage = 1; // Reset to first page on search
    renderTags();
  });

  $('#clearSkillSearch').on('click', function() {
    $(searchInput).val('');
    currentPage = 1;
    renderTags();
  });

  // Initial render on page load
  renderTags();

  // --- File Handling ---
  if (fileInput) {
    fileInput.addEventListener('change', handleFileSelect);
  }
  if(removeFileBtn) {
    removeFileBtn.addEventListener('click', removeFile);
  }

  function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file && fileNameEl && fileSizeEl && filePreview) {
      fileNameEl.textContent = file.name;
      fileSizeEl.textContent = formatFileSize(file.size);
      filePreview.classList.add('show');
    }
  }

  function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }

  function removeFile() {
    if (fileInput) fileInput.value = '';
    if (filePreview) filePreview.classList.remove('show');
  }

  // --- Form Submission ---
  $('#addAchievementForm').on('submit', function(event) {
    if (!selectedTag) {
      alert('Please select a skill/tag first');
      event.preventDefault();
      return;
    }
  });

  // --- Original AJAX Logic for Adding Tags ---
  $('#saveTagBtn').on('click', function (e) {
    e.preventDefault();
    var $btn = $(this);
    var name = $('#tagName').val().trim();
    var category = $('#tagCategory').val();
    if (!name) {
      alert('Please enter a tag name.');
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
        // Create and prepend the new tag chip
        const newTag = `
          <div class="tag-chip" data-id="${res.tblskill_id}" data-name="${res.name}" data-category="${res.category}">
            <span>${res.name}</span>
            <span class="tag-category">${res.category}</span>
          </div>
        `;
        $(tagsContainer).prepend(newTag);

        // Auto-select the new tag
        const newChip = $(tagsContainer).find('.tag-chip:first-child');
        newChip.trigger('click');

        // Close modal
        $('#addTagModal').modal('hide');
        $('#tagName').val('');
        $('#tagCategory').val('Non-Academic');
      } else {
        alert((res && res.msg) ? res.msg : 'Error adding tag.');
      }
    }, 'json').fail(function () {
      $btn.prop('disabled', false);
      alert('Request failed. Please try again.');
    });
  });

})(jQuery);