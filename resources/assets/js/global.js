$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
})
$(function () {
    // Activate popups
    $('.has-popup').popup()
    $('select.dropdown').dropdown({forceSelection: false})


    // Gives us the possibility to create own close modal buttons
    $(".close-modal").click(function () {
        $(this).closest(".ui.modal").modal("hide")
    })

    // Enable the shortcut to the app switcher
    if($('.s-mainAppTitle').length) {
      document.addEventListener('keydown', function(e) {
        if(e.altKey && e.ctrlKey && e.key === 'k') {
          window.location.href = '/appswitcher#/appswitcher'
        }
      })
      $('.s-mainAppTitle').popup({
        content: 'strg+alt+k'
      })
    }

    // Make the message blocks close on click onto the cross
    $('.message .close')
        .on('click', function() {
            $(this).closest('.message').transition('fade')
            var identifier = $(this).closest('.message').attr('data-msg-permanent')
            if (identifier) {
                localStorage.setItem('msg-' + identifier + '-dismissed', true)
            }
        })
    $('.message[data-msg-permanent]').each(function() {
        var identifier = $(this).closest('.message').attr('data-msg-permanent')
        if (!localStorage.getItem('msg-' + identifier + '-dismissed')) {
            $(this).show()
        }
    })

    // if we have semantic ui tabs, we want them to actually work
    $('.tabular.menu .item[data-tab]').tab()

    //Navigation
    $('.submenu').on('click', function (e) {
      var target = e.target
      if (target.tagName.toLowerCase() === 'a') {
        return
      }

      var element = $(this)
      if (element.hasClass('section')) {
        element.next().toggle(250)
        element.toggleClass('active-submenu')
      } else {
        element.find('.submenu-entries').toggle(250)
        element.toggleClass('active-submenu')
      }
    })
    $('.submenu .submenu-entries').on('click', function (e) {
      e.stopPropagation()
    })
})

function getQueryParameters() {
  if (location.search.length === 0) {
    return null
  }

  var items = location.search.substr(1).split("&")
  var result = {}
  var tmp = []

  for (var index = 0; index < items.length; index++) {
    tmp = items[index].split("=")
    if (tmp) {
      result[tmp[0]] = decodeURIComponent(tmp[1])
    }
  }

  return result
}

/**
 * Returns a parameter from the query string
 *
 * @param val The name of the parameter
 * @returns {undefined|string}
 */
function getQueryParameter(val) {
    var parameters = getQueryParameters()
    if (!parameters || parameters.length == 0) {
      return null
    }
    return parameters[val]
}

function updateUrlWithId(id) {
    var url = URI(window.location.href).setQuery('edit', id)
    history.pushState(null, '', url)
}

if (window !== window.parent) {
  // Since there is no universal event to detect
  // vanilla browser navigation, we send an event on load
  window.parent.postMessage({
    type: 'keelearning-iframe-loaded',
    hash: window.location.hash,
    path: window.location.pathname,
    search: window.location.search,
    appId: window.VUEX_STATE.appId,
  }, '*')

  // Simulate the loaded event on click on an `a` tag to save one roundtrip
  // Unfortunately we have no way to hook into `window.location.href = ...` calls
  document.addEventListener('click', function(event) {
    let targetElement = event.target
    while (!targetElement.href && targetElement.parentElement) {
      targetElement = targetElement.parentElement
    }
    // skip if no link, or external link
    if (!targetElement.href || targetElement.host !== window.location.host || targetElement.target === '_blank') {
      return
    }
    // skip if we don't want to forward this to the new backend
    if(targetElement.classList.contains("js-no-relaunch-forwarding")) {
      return
    }
    event.preventDefault()
    window.parent.postMessage({
      type: 'keelearning-iframe-loaded',
      hash: targetElement.hash,
      path: targetElement.pathname,
      search: targetElement.search,
      appId: window.VUEX_STATE.appId,
    }, '*')
  })
}
