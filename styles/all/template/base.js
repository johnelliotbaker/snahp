// Clipboard
var Clipboard = {};

Clipboard.copy_selector = function (selector) {
  // https://stackoverflow.com/questions/46041831/copy-to-clipboard-with-break-line/46151806
  var strn = $(selector).html();
  this.copy(strn);
};

Clipboard.copy = function (strn) {
  // https://stackoverflow.com/questions/46041831/copy-to-clipboard-with-break-line/46151806
  var $temp = $("<textarea>");
  var brRegex = /<br\s*[\/]?>/gi;
  $("body").append($temp);
  $temp.val(strn.replace(brRegex, "\r\n")).select();
  document.execCommand("copy");
  $temp.remove();
};

Clipboard.copy_gallery_link = function (ev) {
  $target = $(ev.target);
  $link = $target.parent().next();
  var href = $link.attr("href");
  this.copy(href);
};

// Avatar
var Avatar = {};
Avatar.append_copy_controls = function () {
  $v = $(".avatar-container").next();
  $v.each((i) => {
    $elem = $($v[i]);
    var text = $elem.text();
    $elem.after(
      `<span onClick="Clipboard.copy('${text}')" class="pointer noselect" style="margin-left: 4px;"><i class="fa fa-clipboard" aria-hidden="true"></i></span>`
    );
  });
};

// Code box
var Codebox = {};
Codebox.append_copy_controls = function () {
  $controlbox = $("div.codebox > p");
  $controlbox.append(
    '<a style="float: right;" onClick="Codebox.copy(event);" class="pointer noselect">Copy</a>'
  );
};

Codebox.copy = function (e) {
  $target = $(e.target);
  var text = $target.text();
  $pre = $target.parent().next();
  var text = $pre.find("code").text();
  Clipboard.copy(text);
};

Codebox.append_resize_controls = function () {
  $controlbox = $("div.codebox > p");
  $controlbox.append(
    '<a style="float: right; margin-left: 8px;" onClick="Codebox.expand(event);" class="pointer noselect">Expand</a>'
  );
};

Codebox.expand = function (e) {
  $target = $(e.target);
  var text = $target.text();
  $pre = $target.parent().next();
  $code = $pre.find("code");
  if (text === "Expand") {
    $target.text("Restore");
    var height0 = $pre.height();
    $pre.data("height0", height0);
    $pre.addClass("expand");
    $pre.css({ height: "500px" });
    $code.addClass("expand");
  } else {
    $target.text("Expand");
    $pre.height($pre.data("height0"));
    $pre.removeClass("expand");
    $code.removeClass("expand");
  }
};

// Utility functions that work with forms
Form_util = {};
Form_util.setup_ctrlenter_quickreply = function () {
  $msgbox = $("#message-box textarea.inputbox");
  $msgbox.keydown((e) => {
    if (e.ctrlKey && e.keyCode == 13) {
      $('input[name="post"]').click();
    }
  });
};

Form_util.disable_resubmit = function () {
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
  }
};

$(function () {
  Form_util.setup_ctrlenter_quickreply();
});

function getAtUsername(event) {
  $self = $(event.target);
  var j = (i = $self[0].selectionStart);
  var text = $mbox.val().toLowerCase();
  while (text.charCodeAt(j - 1) != 10 && text[j - 1] != " " && j > 0) {
    j--;
  }
  var word = text.slice(j, i);
  var n = word.length;
  if (n > 1 && word.slice(0, 2) == "@@") {
    var username = word.slice(2);
    return [username, j, i];
  }
  return null;
}

function collectAvatarUsername() {
  $a = $("div.avatar-container").next();
  var res = [];
  var count = 0;
  var op = [];
  $a.each((e) => {
    var username = $($a[e]).text();
    if (count == 0) {
      op.push(username);
    } else {
      res.push(username);
    }
    count++;
  });
  res.sort((a, b) => {
    return a.length - b.length;
  });
  res = op.concat(res);
  return res;
}

// Pressing escape esc will close post generator modal
$("body").keydown(function (event) {
  if (event.keyCode == 27) {
    event.preventDefault();
    $(".modal_imdb").remove();
    $("#anilist_dialog").remove();
    $("#googlebooks_dialog").remove();
    $("#goodreads_dialog").remove();
    $("#gamespot_dialog").remove();
    $("#mydramalist_dialog").remove();
  }
});

const Spoilers = {
  init: () => {
    $(document).on("click", ".spoiler-wrapper", function (e) {
      $(this).toggleClass("spoiler-wrapper");
      e.preventDefault();
    });
    $(document).on("click", ".spoiler", function (e) {
      $(this).toggleClass("spoiler");
      e.preventDefault();
    });
  },
};
Spoilers.init();

$(document).ready(() => {
  // Add clipboarding to avatar usernames
  Avatar.append_copy_controls();
  // Allow codebox to resize
  Codebox.append_resize_controls();
  Codebox.append_copy_controls();
  // @@username completion
  var usernames = collectAvatarUsername();
  $mbox = $('textarea[name="message"]');
  $mbox.keydown((e) => {
    var maxi = 30;
    if (e.keyCode == 9) {
      e.preventDefault();
      var res = [];
      var targetname = getAtUsername(e);
      if (!targetname) return false;
      if (targetname[0] == "") {
        targetname[0] = usernames[0].toLowerCase();
      }
      for (var username of usernames) {
        if (username.toLowerCase().indexOf(targetname[0]) == 0) {
          $self = $(e.target);
          var text = $self.val();
          var before = text.substr(0, targetname[1]);
          var after = text.substr(targetname[2]);
          text = before + "@@" + username + after;
          $self.val(text);
          var cursorpos = username.length + targetname[1] + 2;
          $self[0].selectionStart = cursorpos;
          $self[0].selectionEnd = cursorpos;
          return true;
        }
      }
    }
  });
  // Hexagon theme. When notification badge turns 0, remove element
  $("body").on("DOMSubtreeModified", "strong.navbar_counter.badge", (e) => {
    $badge = $("strong.navbar_counter.badge");
    if ($badge.text() == "0") {
      $badge.remove();
    }
  });
  // Remove Unanswered topics from quick links
  $('span:contains("Unanswered topics")').closest("li").remove();
  // Auto focus on username when logged out
  $("input#username").focus();
  // remove quick reply's automatic signature attachment
  $("input:hidden[name='attach_sig']").remove();
  // Insert Your Topics
  var user_id = $('[name="snp_user_id"]').val();
  var your_topics =
    `<li><a href="/search.php?author_id=` +
    user_id +
    `&sr=topics&sf=firstpost" role="menuitem"><i class="icon fa-file-o fa-fw icon-gray" aria-hidden="true"></i><span>Your topics</span></a></li>`;
  if (user_id > 1) {
    $('span:contains("Your posts")').closest("li").after(your_topics);
  }
  // Curlies
  $searchbox_master = $(".search_master").keyup((e) => {
    $rows = $("table.autofill").find("tr");
    var searchterm = $(e.target).val().toLowerCase().split(/\s+/);
    for (var i = 0; i < $rows.length; i++) {
      for (var st of searchterm) {
        $row = $rows.slice(i, i + 1);
        $entry_text = $row.text().toLowerCase();
        if (!$entry_text.includes(st)) {
          $row.addClass("hidden");
          break;
        }
        $row.removeClass("hidden");
      }
    }
  });
  $searchbox = $("input.autofill").keyup((e) => {
    var element_id = $(e.target).attr("id");
    var uuid = element_id.match(/(\S+)_(\S+)/)[2];
    if (!uuid) return false;
    $rows = $("#table_" + uuid).find("tr");
    var searchterm = $(e.target).val().toLowerCase().split(/\s+/);
    for (var i = 0; i < $rows.length; i++) {
      for (var st of searchterm) {
        $row = $rows.slice(i, i + 1);
        $entry_text = $row.text().toLowerCase();
        if (!$entry_text.includes(st)) {
          $row.addClass("hidden");
          break;
        }
        $row.removeClass("hidden");
      }
    }
  });
});
