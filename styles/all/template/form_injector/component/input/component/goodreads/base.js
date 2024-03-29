var Goodreads = {};

Goodreads.get_pub_date = function (data) {
  var a_name = ["publication_year", "publication_month", "publication_day"];
  return a_name
    .filter((key) => key in data)
    .map((x) => data[x])
    .join("-");
};

Goodreads.make_authors_html_with_compact_gallery = function (data) {
  try {
    var authors = data.authors.author;
    if (Array.isArray(authors)) {
      var res = [];
      for (var author of authors) {
        var url = author.link;
        var thumbnail = author.image_url;
        var name = author.name;
        res.push(
          [name, "", url, thumbnail].join("`").replace(/(\r\n|\n|\r)/gm, "")
        );
      }
      var entry = res.join("\n");
    } else {
      var url = authors.link;
      var thumbnail = authors.image_url;
      var name = authors.name;
      var entry = [name, "", url, thumbnail]
        .join("`")
        .replace(/(\r\n|\n|\r)/gm, "");
    }
    var start =
      "[center][b][size=80]by[/size][/b][/center]\n\n{snahp}{gallery_compact_sm_c}\n";
    var end = "{/gallery_compact_sm_c}{/snahp}";
    return start + entry + end;
  } catch (e) {
    return "";
  }
};

Goodreads.make_isbn = function (data) {
  try {
    var isbn = data.isbn;
    var isbn13 = data.isbn13;
    let res = "";
    if (isbn.length > 0) {
      res = `[color=#FF8000][b]ISBN[/b][/color]: ${isbn}`;
      if (isbn13.length > 0) {
        res += ` (ISBN13: ${isbn13})\n`;
      }
    }
    return res;
  } catch (e) {
    return "";
  }
};

Goodreads.make_authors_html = function (data) {
  try {
    var authors = data.authors.author;
    if (Array.isArray(authors)) {
      authors = authors.map((elem, index) => {
        return elem.name;
      });
      var entry = authors.slice(0, -1).join(", ") + " & " + authors.slice(-1);
    } else {
      var entry = authors.name;
    }
    var a = `[b][size=110]by[/size][/b]\n\n[size=200][color=#ff4000]${entry}[/color][/size]\n`;
    return a;
  } catch (e) {
    return "";
  }
};

Goodreads.makeTemplate = function (data) {
  try {
    var thumbnail = data.image_url.replace(/SX[0-9]+/gi, "SX2000");
  } catch (e) {
    var thumbnail = "";
  }
  try {
    var title = data.title;
  } catch (e) {
    var title = "";
  }
  try {
    var authors = data["authors"];
  } catch (e) {
    var authors = "";
  }
  try {
    var averageRating = data.average_rating;
  } catch (e) {
    var averageRating = "";
  }
  try {
    var url = data.link;
  } catch (e) {
    var url = "";
  }
  try {
    var categories = data["categories"];
  } catch (e) {
    var categories = "";
  }
  try {
    var description = data.description.replace(/(<([^>]+)>)/gi, " ");
  } catch (e) {
    var description = "";
  }
  try {
    var language = toTitleCase(data.language_code);
  } catch (e) {
    var language = "";
  }
  try {
    var pageCount = data.num_pages;
  } catch (e) {
    var pageCount = "";
  }
  try {
    var printType = toTitleCase(data["printType"]);
  } catch (e) {
    var printType = "";
  }
  var publishedDate = this.get_pub_date(data);
  try {
    var publisher = data.publisher;
  } catch (e) {
    var publisher = "";
  }
  try {
    var ratingsCount = data.ratings_count;
  } catch (e) {
    var ratingsCount = "";
  }
  var thumbnail = getEntryOrEmpty(
    `[url={url}][himg=370]{text}[/himg][/url]\n`,
    thumbnail,
    url
  );
  var title = getEntryOrEmpty(
    `[size=180][b][nurl={url}]{text}[/nurl][/b][/size]\n`,
    title,
    url
  );
  var authors = getEntryOrEmpty(
    `[b][size=110]by[/size]\n\n[size=200]{text}[/size][/b]\n`,
    joinArrayOrEmpty(authors, ", ")
  );
  var authors = this.make_authors_html(data);
  var averageRating = getEntryOrEmpty(
    `[size=108][b]{text}[/b] / [b]5[/b][/size] (based on [b]${ratingsCount}[/b] ratings)\n`,
    averageRating
  );
  var categories = getEntryOrEmpty(
    `[b][size=140]{text}[/size][/b]\n`,
    joinArrayOrEmpty(categories, ", ")
  );
  var description = getEntryOrEmpty(`{text}\n`, description);
  var publishedDate = getEntryOrEmpty(
    `[color=#FF8000][b]Published Date[/b][/color]: {text}\n`,
    publishedDate
  );
  var printType = getEntryOrEmpty(
    `[color=#FF8000][b]Print Type[/b][/color]: {text}\n`,
    printType
  );
  var language = getEntryOrEmpty(
    `[color=#FF8000][b]Language[/b][/color]: {text}\n`,
    language
  );
  var publisher = getEntryOrEmpty(
    `[color=#FF8000][b]Publisher[/b][/color]: {text}\n`,
    publisher
  );
  var pageCount = getEntryOrEmpty(
    `[color=#FF8000][b]Page Count[/b][/color]: {text}\n`,
    pageCount
  );
  var ratingsCount = getEntryOrEmpty(
    `[color=#FF8000][b]Ratings Count[/b][/color]: {text}\n`,
    ratingsCount
  );
  var ddl = `[color=#0000FF][b]Direct Download Links[/b][/color]: \n`;
  var hidebox = `[size=116][hide][nurl=\n][size=150][color=#FF0000][b]MEGA[/b][/color][/size][/nurl][/hide][/size]\n`;
  var isbn = this.make_isbn(data);
  var text =
    "[center]\n" +
    thumbnail +
    "\n" +
    title +
    "\n" +
    authors +
    "\n" +
    averageRating +
    "\n" +
    hidebox +
    "[/center]\n\n\n" +
    description +
    "\n" +
    publishedDate +
    publisher +
    language +
    pageCount +
    isbn +
    "\n";
  text = text.replace(/(<br>|<br\/>|<br \/>)/g, "");
  text = text.replace(/(\r?\n|\r){5,99}/g, "\n\n\n\n");
  return text;
};

Goodreads.fillMessage = function (entry) {
  var book_id = entry.best_book.id;
  var url = "/app.php/snahp/api_proxy/goodreads/?cmd=book&bid=" + book_id;
  $.get(url).done((resp) => {
    var summary = Goodreads.makeTemplate(resp);
    var text = summary;
    $("#message").val(text);
  });
};

Goodreads.updatePosters = function (media) {
  $goodreads_dialog = $("#goodreads_dialog");
  $goodreads_header = $("#goodreads_header");
  $goodreads_title = $("#goodreads_title");
  $goodreads_content = $("#goodreads_poster_list").empty();
  var count = 0;
  for (var entry of media) {
    title = entry.best_book.title;
    author = entry.best_book.author.name;
    pubDate = entry.original_publication_year;
    img = entry.best_book.image_url;
    $li = $("<li/>").addClass("img_li").appendTo($goodreads_content);
    $imgDiv = $("<div/>").addClass("img_container").appendTo($li);
    $img = $("<img/>")
      .attr({
        id: "img-" + count,
        src: img,
      })
      .width("150")
      .height("225")
      .click(function (e) {
        target = e.target;
        var tid = $(target).attr("id");
        var match = tid.match(/img-(\d+)/);
        tid = parseInt(match[1], 10);
        var goodreadsid = $(target).attr("goodreadsid");
        Goodreads.fillMessage(media[tid]);
        $("#goodreads_dialog").remove();
      })
      .appendTo($imgDiv);
    $type_txt = $("<div/>")
      .addClass("bottom-right")
      .html(
        `
            ${title}<br>
            ${pubDate}<br>
            ${author}<br>
        `
      )
      .appendTo($imgDiv);
    count++;
  }
  if (count == 0) {
    $notfound = $("<h>Not Found</h>").css({ "font-size": "200%" });
    $goodreads_content.append($notfound);
  }
  $('<a href="#goodreads_dialog" id="modalTriggerLink"></a>').appendTo(
    $("body")
  );
  setTimeout(function () {
    $("#modalTriggerLink")[0].click();
    $("#modalTriggerLink").remove();
    var match = /(.*)(#.*)/.exec(window.location.href);
    if (match && match[1]) {
      window.history.replaceState("", "", match[1]);
    }
  }, 100);
};

Goodreads.goodreads_dialog_template = `
<div id="goodreads_dialog" class="twbs modalDialog goodreads">
  <div class="twbs card document rounded">
    <div class="twbs card-body dialog rounded">
      <div class="twbs card-title">
        <h5 id="goodreads_title"></h5>
      </div>
      <div class="twbs card dialog_content" id="goodreads_content">
        <button id="close_btn" type="button" class="twbs dialog_close_btn">
          <span aria-hidden="true">&times;</span>
        </button>
        <div class="twbs card-group dialog_top_menu" id="goodreads_top_filter">
          <div class="twbs card text-center">
            <div class="twbs card-body">
              <div class="twbs row">
                <div class="twbs card col-12">
                  <div class="modal-menu">
                    <input type="checkbox" id="cb_show_goodreads_enable_sort" value="0">
                    <label class="checkbox_label" for="cb_show_goodreads_enable_sort">Sort By Date</label>
                    <input type="checkbox" id="cb_show_goodreads_sort_asc" value="1" checked>
                    <label class="checkbox_label" for="cb_show_goodreads_sort_asc">Newest to Oldest</label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="poster_list" id="goodreads_poster_list">
        </div>
      </div>
    </div>
  </div>
</div>
`;

Goodreads.filterGoodreadsMedia = function (media) {
  var aType = [];
  aType.push("BOOK");
  var selectedMedia = [];
  for (var i in media) {
    var entry = media[i].volumeInfo;
    var pubDate = entry["publishedDate"];
    var match = /(\d{4})/.exec(pubDate);
    media[i].date = Array.isArray(match) ? parseInt(match[0]) : 0;
    var type = entry["printType"];
    if (aType.includes(type)) {
      selectedMedia.push(media[i]);
    }
  }
  if ($("#cb_show_goodreads_enable_sort").prop("checked")) {
    selectedMedia = selectedMedia.sort(function (a, b) {
      if ($("#cb_show_goodreads_sort_asc").prop("checked"))
        return -a.date + b.date;
      return a.date - b.date;
    });
  }
  return selectedMedia;
};

Goodreads.handle_goodreads = function (response, searchTerm) {
  $goodreads_dialog = $(Goodreads.goodreads_dialog_template).appendTo(
    $("body")
  );
  $goodreads_dialog = $("#goodreads_dialog");
  $goodreads_header = $("#goodreads_header");
  $goodreads_title = $("#goodreads_title").text(`Results for "${searchTerm}"`);
  $goodreads_content = $("#goodreads_poster_list");
  $close_btn = $("#close_btn").click(function () {
    $("#goodreads_dialog").remove();
  });
  $("[id^=cb_show_goodreads]").change(function (event) {
    var selectedMedia = Goodreads.filterGoodreadsMedia(media);
    Goodreads.updatePosters(selectedMedia);
  });
  var media = response;
  // var selectedMedia = Goodreads.filterGoodreadsMedia(media);
  Goodreads.updatePosters(media);
};

Goodreads.addSearchQualifier = function (url, dict) {
  for (var key in dict) {
    val = dict[key];
    if (val) {
      switch (key) {
        case "title":
          url += "+intitle:";
          break;
        case "author":
          url += "+inauthor:";
          break;
        case "isbn":
          url += "+isbn:";
          break;
        case "order":
          url += "&orderBy=";
      }
      url += val;
    }
  }
  return url;
};

Goodreads.startHandlingGoodreadsAjax = function () {
  dict = {};
  $goodreads_input = $("#goodreads_input");
  var searchTerm = encodeURI($goodreads_input.val());
  var url = `/app.php/snahp/api_proxy/goodreads/?s=${searchTerm}`;
  $ajax = $.ajax({
    url: url,
    method: "get",
  });
  $ajax.done(function (response) {
    Goodreads.handle_goodreads(response, searchTerm);
  });
};

$(document).ready(function () {
  $("#goodreads_input").keydown(function (event) {
    if (event.keyCode == 13) {
      event.preventDefault();
      Goodreads.startHandlingGoodreadsAjax();
    }
  });
});
