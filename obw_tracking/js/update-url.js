(function ($) {
  window.addEventListener("load", function () {
    var listOptions = [];
    function getID(name, data) {
      jQuery.each(JSON.parse(data), function (index, obj) {
        var causeName = lowerFirstCharacter(obj.label);
        if (name === causeName) {
          listOptions.push(obj.value);
          return;
        }
      });
    }
    function getMainPath(str) {
      const regex = /^\/([^?\/]+)/;
      return str.match(regex)[1];
    }
    function checkFilterUrl() {
      var locationName = getMainPath(window.location.pathname);
      return locationName === "stories" || locationName === "country" || locationName === "cause";
    }
    var urlSearch = window.location.search;
    if (checkFilterUrl()) {
      if (urlSearch) {
        urlSearch = urlSearch.replace(/\?causes=/g, ",");
        urlSearch = urlSearch.replace(/&causes=/g, ",");
        urlSearch = urlSearch.replace(/\?countries=/g, "|");
        urlSearch = urlSearch.replace(/&countries=/g, "|");
        console.log("have url search");
        // console.log(urlSearch); // ,animals,education|bangladesh|vietnam

        var n = urlSearch.indexOf("|");
        var causes_str = "";
        var countries_str = "";
        if (n !== -1) {
          causes_str = urlSearch.substr(0, n);
          countries_str = urlSearch.replace(causes_str, "");
        }

        if (causes_str === "") {
          causes_str = urlSearch;
        }

        // Get array and remove empty value
        var listCauses = causes_str.split(",").filter(function (v) {
          return v !== "";
        });
        var listCountries = countries_str.split("|").filter(function (v) {
          return v !== "";
        });

        listOptions = [];
        $.each(listCauses, function (int, value) {
          getID(value, causesObj);
        });
        $.each(listCountries, function (int, value) {
          getID(value, countriesObj);
        });

        $.each(listOptions, function (ind, optId) {
          if (optId) {
            $('option[value="' + optId + '"]').prop("selected", true);
          }
        });
        console.log($("select"));
        setTimeout(function () {
          $("select").trigger("change");
        }, 1000);
      }
    }
  });
})(jQuery);
