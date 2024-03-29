// For acieeed top header minimizer check:
// styles/all/template/event/overall_header_headerbar_after.html
var content_template =
  '<div id="acieeed_minimizer_content" align="right"></div>';
var resizer_template =
  '<i style="color:#f8c301; font-size:1.4em; cursor:pointer;" aria-hidden="true"></i>';
$header = $("#page-header");
$content = $(content_template).prependTo($header);
$banner = $($("#page-header > div > div")[0]);
$resizer = $(resizer_template);
var height0 = $banner.height();
if (Cookie.get("style", "acieeed.b_minimized")) {
  $resizer.attr("class", "icon fa-caret-down fa-fw");
  $banner.css({ height: "0px", overflow: "hidden" });
} else {
  $resizer.attr("class", "icon fa-caret-up fa-fw");
  $banner.css({ height: height0 + "px", overflow: "hidden" });
}
$resizer.appendTo($content).click((e) => {
  if (Cookie.get("style", "acieeed.b_minimized")) {
    $banner.animate(
      { opacity: 1, height: height0 + "px", overflow: "hidden" },
      700
    );
    $resizer.attr("class", "icon fa-caret-up fa-fw");
    Cookie.set("style", "acieeed.b_minimized", 0);
  } else {
    $resizer.attr("class", "icon fa-caret-down fa-fw");
    $banner.animate({ opacity: 0, height: "0px", overflow: "hidden" }, 700);
    Cookie.set("style", "acieeed.b_minimized", 1);
  }
});
