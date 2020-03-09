/**
 * Copyright (c) 2018 - present, Inbenta Technologies Inc.
 *
 * This source code is licensed under the MIT license found in the
 * LICENSE file in the root directory of this source tree.
 *
 */

(function () {
  /** ------------------------------------------------------------------ */
  /** ------------------------- Rating system -------------------------- */
  /** ------------------------------------------------------------------ */

  /**
   * Function to send tracking event on rating.
   */
  function sendTracking(trackingCode, value, comment) {
    value = value || null;
    comment = comment || null;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/tracking');
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.send(JSON.stringify({ type: 'rate', code: trackingCode, value: value, comment: comment }));
  };

  /**
   * Function to display rating comment step.
   */
  function goToCommentStep(ratingSection, ratingData) {
    var commentSection = ratingSection.querySelector('.inbenta-km__rating__comment');
    var commentButton = commentSection.querySelector('.inbenta-km-button');
    var commentInput = commentSection.querySelector('.comment__feedback__textarea');

    // We resets input for accepting new comment.
    commentInput.value = '';
    commentButton.classList.toggle('inbenta-km-button—-disabled');
    commentSection.classList.toggle('inbenta-km__rating__comment--hidden');
    commentInput.addEventListener('keydown', function () {
      // User can send his comment only if it is not empty.
      if (commentInput.value !== '') {
        commentButton.classList.remove('inbenta-km-button—-disabled');
      }
    });

    // We send a first tracking event to log value without comment.
    sendTracking(ratingData[1].value, ratingData[0].value);

    commentButton.addEventListener('click', function () {
      commentSection.classList.toggle('inbenta-km__rating__comment--hidden');
      goToThanksStep(ratingSection, ratingData, commentInput.value);
    });
  }

  /**
   * Function to display rating thanks step.
   */
  function goToThanksStep(ratingSection, ratingData, comment) {
    var thanksSection = ratingSection.querySelector('.inbenta-km__rating__thanks');
    thanksSection.classList.toggle('inbenta-km__rating__thanks--hidden');
    sendTracking(ratingData[1].value, ratingData[0].value, comment);
  }

  /**
   * Function to process next rating step (display comment section, send tracking, ...).
   */
  function processRatingNextStep(ratingSection, buttonsSection, ratingData) {
    buttonsSection.classList.toggle('inbenta-km__rating__content--hidden');
    if (ratingData[2].value === '1') {
      goToCommentStep(ratingSection, ratingData);
    } else {
      goToThanksStep(ratingSection, ratingData);
    }
  }


  /** ------------------------------------------------------------------ */
  /** -------------------------- Autocomplete -------------------------- */
  /** ------------------------------------------------------------------ */

  /**
   * Return index of currently focused autocomplete result
   */
  function getFocusedResult(autocompleteResultNodes) {
    var currentFocusIndex = -1;
    for (var index = 0; index < autocompleteResultNodes.length; ++index) {
      var autocompleteResultNode = autocompleteResultNodes[index];
      if (autocompleteResultNode === document.activeElement) {
        currentFocusIndex = index;
      }
    }
    return currentFocusIndex;
  }

  /**
   * Focus the next result in autocomplete dropdown
   */
  function focusNextResult() {
    var autocompleteResultNodes = document.querySelectorAll('.inbenta-km__autocompleter__link');
    if (autocompleteResultNodes.length > 0) {
      var nextFocusIndex = getFocusedResult(autocompleteResultNodes) + 1;
      // Stay on last result if the user continue to tap on Down Key
      nextFocusIndex = Math.min(autocompleteResultNodes.length - 1, nextFocusIndex);
      autocompleteResultNodes[nextFocusIndex].focus();
    }
  }

  /**
   * Focus the previous result in autocomplete dropdown
   */
  function focusPrevResult(searchInputNode) {
    var autocompleteResultNodes = document.querySelectorAll('.inbenta-km__autocompleter__link');
    if (autocompleteResultNodes.length > 0) {
      var previousFocusIndex = getFocusedResult(autocompleteResultNodes) - 1;
      // Stay on search input if the user continue to tap on Up Key
      if (previousFocusIndex < 0) {
        searchInputNode.focus();
      } else {
        autocompleteResultNodes[previousFocusIndex].focus();
      }
    }
  }

  /**
   * Function executed to request API and load results when the user finishes to type in search input
   */
  function getAutocompleteResults(query, autocompleteNode) {
    if (query.trim() !== '') {
      var xhr = new XMLHttpRequest();
      xhr.open('GET', '/autocomplete?query=' + query);
      xhr.setRequestHeader('Content-Type', 'application/json');
      xhr.onload = function () {
        if (xhr.status === 200) {
          formatAutocompleteResponse(JSON.parse(xhr.responseText), autocompleteNode);
        }
      };
      xhr.send();
    }
  }

  /**
   * Format Autocomplete dropdown content by creating html elements (ul > li > a)
   */
  function formatAutocompleteResponse(response, autocompleteNode) {
    // Remove all previous results in autocomplete dropdown list
    while (autocompleteNode.firstChild) {
      autocompleteNode.removeChild(autocompleteNode.firstChild);
    }
    for (var index = 0; index < response.length; ++index) {
      var result = response[index];
      // Insert HTML generated in autocomplete dropdown list
      autocompleteResult = document.createElement('a');
      autocompleteResult.className = 'inbenta-km__autocompleter__link';
      autocompleteResult.tabindex = index;
      autocompleteResult.href = result.seoFriendlyUrl;
      autocompleteResult.innerHTML = result.titleHighlight;
      autocompleteNode.appendChild(autocompleteResult);
    }
    // Show the autocomplete dropdown if there is results
    if (response.length > 0) {
      showAutocompleteDropDown(autocompleteNode);
    }
  }

  /**
   * Function to Show autocomplete dropdown
   */
  function showAutocompleteDropDown(autocompleteNode) {
    // Check if there is results in the autocomplete dropdown before display it
    if (document.querySelectorAll('.inbenta-km__search__form .inbenta-km__autocompleter .inbenta-km__autocompleter__link').length > 0) {
      autocompleteNode.classList.remove('inbenta-km-hidden');
    }
  }

  /**
   * Function to Hide autocomplete dropdown
   */
  function hideAutocompleteDropdown(searchInputNode, autocompleteNode) {
    searchInputNode.blur();
    autocompleteNode.classList.add('inbenta-km-hidden');
  }

  /**
   * Submit the autocomplete
   */
  function submitAutocomplete(event) {
    var autocompleteResultNodes = document.querySelectorAll('.inbenta-km__autocompleter__link');
    if (autocompleteResultNodes.length > 0) {
      var currentFocusIndex = getFocusedResult(autocompleteResultNodes);
      // Forcing navigation to the focused result page if applicable...
      if (currentFocusIndex >= 0) {
        event.preventDefault();
        window.location = autocompleteResultNodes[currentFocusIndex].href;
      }
    }
  }


  /**
   * Main function.
   */
  window.onload = function () {
    // ============================== Event Listeners for ratings =================================
    var ratingSections = document.querySelectorAll('.inbenta-km__rating');
    // For each rating section...
    for (var ratingIndex = 0; ratingIndex < ratingSections.length; ++ratingIndex) {
      var ratingSection = ratingSections[ratingIndex];
      var buttonsSection = ratingSection.querySelector('.inbenta-km__rating__content');
      // For each content rating button...
      var buttonWrappers = buttonsSection.querySelectorAll('.content__buttons__button-wrapper');
      for (var index = 0; index < buttonWrappers.length; ++index) {
        var buttonWrapper = buttonWrappers[index];
        var ratingData = buttonWrapper.querySelectorAll('.rating-data');
        var button = buttonWrapper.querySelector('.inbenta-km-button');
        button.addEventListener('click', (function (ratingSection, buttonsSection, ratingData) {
          return function () {
            processRatingNextStep(ratingSection, buttonsSection, ratingData);
          };
        })(ratingSection, buttonsSection, ratingData));
      }
    }


    // ============================ Event Listeners for autocomplete ==============================
    // Detect when the user finishes to type in the search input to send the request
    // and get the results from the API
    var typingTimer;
    var doneTypingInterval = 200;
    var searchFormNode = document.querySelector('.inbenta-km__search__form');
    var searchInputNode = document.querySelector('.inbenta-km__search__form .inbenta-km-input');
    var autocompleteNode = document.querySelector('.inbenta-km__search__form .inbenta-km__autocompleter');
    var formButtonNode = document.querySelector('.inbenta-km__search__form .form__button .inbenta-km-button');

    if (searchInputNode && autocompleteNode && formButtonNode && searchFormNode) {
      // Open the autocomplete dropdown when the user focus on search input

      searchInputNode.focus(function () {
        return showAutocompleteDropDown(autocompleteNode);
      });
      // Open the autocomplete dropdown when the user clicks on search input

      searchInputNode.addEventListener('click', function (e) {
        showAutocompleteDropDown(autocompleteNode);
        e.stopPropagation(); // Stop Propagation $(document).click()
      });

      // On keyup in the search input, start the countdown
      searchInputNode.addEventListener('keyup', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(function () {
          var query = searchInputNode.value;
          getAutocompleteResults(query, autocompleteNode)
        }, doneTypingInterval);
      });

      // On keydown in the search input, clear the countdown
      searchInputNode.addEventListener('keydown', function (event) {
        clearTimeout(typingTimer);
        if (event.target.value !== '') {
          formButtonNode.classList.remove('inbenta-km-button—-disabled');
        }
      });

      // Submit search query when clicking on search button
      formButtonNode.addEventListener('click', function () {
        searchFormNode.submit();
      });

      // Hide the dropdown menu when the user click outside the autocomplete component
      document.addEventListener('click', function () {
        return hideAutocompleteDropdown(searchInputNode, autocompleteNode);
      });

      // Check when the user press a key to navigate in the autocomplete dropdown (Up,Down,Enter,Escape,Tab)
      document.addEventListener("keydown", function (e) {
        var searchInputIsFocused = (searchInputNode === document.activeElement);
        var autocompleteIsFocused = searchInputIsFocused;
        // Checking if one of the autocompleter results is focused...
        var autocompleteResultNodes = document.querySelectorAll('.inbenta-km__autocompleter__link');
        for (var index = 0; index < autocompleteResultNodes.length; ++index) {
          var autocompleteResultNode = autocompleteResultNodes[index];
          if (autocompleteResultNode === document.activeElement) {
            autocompleteIsFocused = true;
          }
        }
        if (autocompleteIsFocused) {
          switch (e.which) {
            case 40: // Down key
              e.preventDefault();
              focusNextResult();
              break;
            case 38: // Up key
              e.preventDefault();
              focusPrevResult(searchInputNode);
              break;
            case 13: // Enter key
              submitAutocomplete(e);
              break;
            case 27: // Escape key
              e.preventDefault();
              hideAutocompleteDropdown(searchInputNode, autocompleteNode);
              break;
            case 9: // Tab key
              e.preventDefault();
              hideAutocompleteDropdown(searchInputNode, autocompleteNode);
              break;
            default: // If user continue to type and a result is focused
              searchInputNode.focus();
          }
        }
      });
    }
  };
})();
