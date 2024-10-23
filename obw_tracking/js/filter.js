(function($, Drupal, drupalSettings) {
    var running_count = 1;

    var element_add_filter = {
        element_type: "filter",
        common_fields: {
            event: "content_filter",
            eventCategory: "stories",
            eventAction: "Select Filter",
        },
        list_elements: [{
                id: "select#edit-causes",
                element_type: "causes",
            },
            {
                id: "select#edit-countries",
                element_type: "countries",
            },
        ],
    };
    var element_remove_filter = {
        element_type: "filter",
        common_fields: {
            event: "content_filter",
            eventCategory: "stories",
            eventAction: "Remove Filter",
        },
        select_2: {
            cause: {
                id: "select#edit-causes",
            },
            country: {
                id: "select#edit-countries",
            },
        },
        list_elements: [{
                id: "select#edit-causes",
                element_type: "causes",
            },
            {
                id: "select#edit-countries",
                element_type: "countries",
            },
        ],
    };
    var element_clear_all_filter = {
        element_type: "clear_filter",
        common_fields: {
            event: "content_filter",
            eventCategory: "stories",
            eventAction: "Clear all Filters",
        },
        list_elements: [{
            id: ".clear-all-filter",
            element_type: "causes",
        }, ],
    };

    function idsSelected(id) {
        var selected_items = $(id).select2("data");
        var id_selected = [];
        $(selected_items).each(function() {
            id_selected.push(this.id);
        });
        return id_selected.join(",");
    }

    function unSelected(data, element) {
        var cause_selected = idsSelected(element.list_elements[0].id);
        var country_selected = idsSelected(element.list_elements[1].id);

        var custom_dynamic_fields = {
            eventLabel: "Remove: " + data.id,
            filter: "actions=;causes=" +
                cause_selected +
                ";countires=" +
                country_selected +
                ";tags=",
        };
        console.log("unSelected -> custom_dynamic_fields", custom_dynamic_fields);

        Drupal.custom.trackingCommon.handle_layer_item(
            element, {},
            custom_dynamic_fields
        );
    }

    function trackingUnSelected(data, element) {
        var cause_selected = idsSelected(element.list_elements[0].id, data.id);
        var country_selected = idsSelected(element.list_elements[1].id, data.id);
        // console.log("data:", data);

        var custom_dynamic_fields = {
            eventLabel: "Remove: " + data.id,
            filter: "actions=;causes=" +
                cause_selected +
                ";countires=" +
                country_selected +
                ";tags=",
        };

        Drupal.custom.trackingCommon.handle_layer_item(
            element, {},
            custom_dynamic_fields
        );
    }

    // select multi stories quất thử đi
    function select() {
        var selectElement = $("select");
        var selectMultipleAttr = selectElement.attr("multiple")
        if (typeof selectMultipleAttr !== typeof undefined && selectMultipleAttr !== false) {
            selectElement
                .select2({ tags: true })
                .on("change", function(event) {
                    // clone tags into another div
                    var parent = $(this).parent();

                    if (parent.hasClass("form-item-causes")) {
                        var $selected = $(this).find("option:selected");
                        var $container = $(".js-cause-tags-container");
                        var $list = $("<ul>");

                        $selected.each(function(k, v) {
                            var id = $(this).val();
                            var $li = $(
                                '<li class="tag-selected" data-id="' +
                                id +
                                '"><a class="destroy-tag-selected">×</a><span>' +
                                $(v).text() +
                                "</span></li>"
                            );
                            $li.children("a.destroy-tag-selected").on("click", function(e) {
                                trackingUnSelected({
                                        id: id,
                                    },
                                    element_remove_filter
                                );
                                $(this).parent().remove();
                                parent
                                    .find('select option[value="' + id + '"]')
                                    .prop("selected", false)
                                    .trigger("change");
                            });
                            $list.append($li);
                        });
                        $container.html("").append($list);
                    } else if (parent.hasClass("js-form-item-countries")) {
                        var _$selected = $(this).find("option:selected");
                        var _$container = $(".js-countries-tags-container");
                        var _$list = $("<ul>");
                        _$selected.each(function(k, v) {
                            var id = $(this).val();
                            var $li = $(
                                '<li class="tag-selected" data-id="' +
                                id +
                                '"><a class="destroy-tag-selected">×</a><span>' +
                                $(v).text() +
                                "</span></li>"
                            );
                            $li.children("a.destroy-tag-selected").on("click", function(e) {
                                trackingUnSelected({
                                        id: id,
                                    },
                                    element_remove_filter
                                );
                                var desc = $(this).parent().find("span").html();
                                // console.log(desc);
                                $(this).parent().remove();
                                parent
                                    .find('select option[value="' + id + '"]')
                                    .prop("selected", false)
                                    .trigger("change");
                            });
                            _$list.append($li);
                        });
                        _$container.html("").append(_$list);
                    }

                    if ($(".tags-container li").length > 0) {
                        $(".tags-container .clear-all-filter").addClass("show");
                    } else {
                        $(".tags-container .clear-all-filter").removeClass("show");
                    }

                    // onchange count again option selected
                    var uldiv = $(this).siblings("span.select2").find(".countContent");
                    var count = $(this).select2("data").length;
                    var parents = $(this).siblings("span.select2").parent();

                    if (count == 0) {
                        if (parents.hasClass("js-form-item-causes")) {
                            uldiv.html("Show all causes");
                        } else if (parents.hasClass("js-form-item-countries")) {
                            uldiv.html("Show all countries");
                        }
                    } else {
                        if (parents.hasClass("js-form-item-causes")) {
                            if (count > 1) {
                                uldiv.html(+count + " causes selected");
                            } else {
                                uldiv.html(+count + " cause selected");
                            }
                        } else if (parent.hasClass("js-form-item-countries")) {
                            if (count > 1) {
                                uldiv.html(+count + " countries selected");
                            } else {
                                uldiv.html(+count + " country selected");
                            }
                        }
                    }
                    //
                    if ($(".tags-container li").length > 0) {
                        $(".tags-wrap").show();
                    } else {
                        $(".tags-wrap").hide();
                    }

                    if ($(".filter-heading").length > 0) {
                        $(".filter-heading .form-actions input").trigger("click");
                    }
                })
                .on("select2:unselect", function(event) {
                    console.log("select -> event", event);
                })
                .data("id")

            // Count option selected
            var countContent = '<div class="countContent"></div>';
            $(".filter span.select2 .select2-selection").append(countContent);
            $(".js-form-item-causes span.select2 .countContent").html(
                "Show all causes"
            );
            $(".js-form-item-countries span.select2 .countContent").html(
                "Show all countries"
            );
            $(".tags-container .clear-all-filter").click(function() {
                $(".wrapper-filler select option")
                    .prop("selected", false)
                    .trigger("change");
                $(".tags-container .clear-all-filter").removeClass("show");
                $(".js-form-item-causes span.select2 .countContent").html(
                    "Show all causes"
                );
                $(".js-form-item-countries span.select2 .countContent").html(
                    "Show all countries"
                );
            });
            if ($(".tags-container li").length > 0) {
                $(".tags-container .clear-all-filter").addClass("show");
            } else {
                $(".tags-container .clear-all-filter").removeClass("show");
            }
        } else {
            $(selectElement).select2({});
            if ($("select").parent(".js-form-item-purpose")) {
                $(".js-form-item-purpose select").select2({
                    dropdownCssClass: "no-search",
                });
            }
        }
    }

    // fiter mobile
    function mobileFilter() {
        var selectElement = $("select");
        var parentClass = $("select").parent();
        // console.log(1);
        $(".js-form-item-causes select option").each(function(k, v) {
            var value = $(this).attr("value");
            var option = $(
                '<div class="col-6 col-item"><div class="filter-select-option" data-id="' +
                value +
                '"><span>' +
                $(v).text() +
                "</span></div></div>"
            );
            // console.log(option);
            $(".filter-mobile-option .cause-item .row").append(option);
        });
        $(".js-form-item-countries select option").each(function(k, v) {
            var value = $(this).attr("value");
            var option = $(
                '<div class="col-6 col-item"><div class="filter-select-option" data-id="' +
                value +
                '"><span>' +
                $(v).text() +
                "</span></div></div>"
            );
            // console.log(option);
            $(".filter-mobile-option .countries-item .row").append(option);
        });
        $(".filter-select-option").on("click", function() {
            var value = $(this).attr("data-id");

            if ($(this).hasClass("selected")) {
                $(this).removeClass("selected");
                parentClass
                    .find('select option[value="' + value + '"]')
                    .prop("selected", false);
            } else {
                $(this).addClass("selected");
                parentClass
                    .find('select option[value="' + value + '"]')
                    .prop("selected", true);
            }
        });
        $(".filter-mobile-footer .clear-all").on("click", function() {
            $(".filter-select-option").removeClass("selected");
            parentClass.find("select option").prop("selected", false);
            $(".filter-heading .form-actions input").trigger("click");
            $(".filter-mobile-option").fadeOut();
        });
        $(".filter-mobile-footer .apply-filters").on("click", function() {
            $(".filter-heading .form-actions input").trigger("click");
            $(".filter-mobile-option").fadeOut();
        });
        $(".wrapp-filter-mobile").on("click", function() {
            $(".filter-mobile-option").fadeIn();
        });
        $(".filter-mobile-heading .close").on("click", function() {
            $(".filter-mobile-option").fadeOut();
        });
        if ($(".filter-heading .filter").hasClass("story_category")) {
            $(".filter-mobile-option").addClass("story_category");
        }
        if ($(".filter-heading .filter").hasClass("story_country")) {
            $(".filter-mobile-option").addClass("story_country");
        }
        $(".filter-mobile-option .item-heading").on("click", function() {
            $(this).toggleClass("toggle");
            $(this).siblings(".row").slideToggle();
        });
    }

    function mobile() {
        var md = new MobileDetect(window.navigator.userAgent);
        var isHaveFilterHeading = $(".filter-heading").length > 0;

        if (md.mobile() && isHaveFilterHeading) {
            $(".filter-heading").addClass("mobile-filter");
            mobileFilter();
        }
    }

    window.addEventListener("load", function() {
        select();
        mobile();
    });

    Drupal.behaviors.pageLoad = {
        attach: function(context, settings) {
            if (running_count !== 1) {
                return;
            }
            running_count++;
            var _this = $;

            var $causeSelect = $(element_remove_filter.select_2.cause.id);
            var $countrySelect = $(element_remove_filter.select_2.country.id);

            $causeSelect.select2();
            $countrySelect.select2();

            // console.log('------ FILTER.js ------');
            // console.time('[time] FILTER.js');
            // console.time('[time] add filter');
            element_add_filter.list_elements.forEach(function(item) {
                _this(item.id).on("select2:select", function(e) {
                    var data = e.params.data;

                    var cause_selected = idsSelected(
                        element_add_filter.list_elements[0].id
                    );
                    var country_selected = idsSelected(
                        element_add_filter.list_elements[1].id
                    );

                    var custom_dynamic_fields = {
                        eventLabel: "Select: " + data.id,
                        filter: "actions=;causes=" +
                            cause_selected +
                            ";countires=" +
                            country_selected +
                            ";tags=",
                    };

                    Drupal.custom.trackingCommon.handle_layer_item(
                        element_add_filter, {},
                        custom_dynamic_fields
                    );
                });
            });
            // console.timeEnd('[time] add filter');
            //
            // console.time('[time] remove filter');
            $causeSelect.on("select2:unselect", function(e) {
                console.log("filter.js => $causeSelect e", e);
                var data = e.params.data;
                unSelected(data, element_remove_filter);
            });
            $countrySelect.on("select2:unselect", function(e) {
                console.log("filter.js => $countrySelect e", e);
                var data = e.params.data;
                unSelected(data, element_remove_filter);
            });

            // console.timeEnd('[time] remove filter');
            //
            // console.time('[time] clear all filter');
            _this(element_clear_all_filter.list_elements[0].id).click(function(
                event
            ) {
                var cause_selected = idsSelected(
                    element_remove_filter.list_elements[0].id
                );
                var country_selected = idsSelected(
                    element_remove_filter.list_elements[1].id
                );
                var event_label = "";

                if (cause_selected && country_selected) {
                    event_label = cause_selected + "," + country_selected;
                } else if (cause_selected) {
                    event_label = cause_selected;
                } else {
                    event_label = country_selected;
                }

                var custom_dynamic_fields = {
                    eventLabel: event_label,
                    filter: "actions=;causes=;countires=;tags=",
                };

                Drupal.custom.trackingCommon.handle_layer_item(
                    element_clear_all_filter, {},
                    custom_dynamic_fields
                );
            });
            // console.timeEnd('[time] clear all filter');
            //
            // console.timeEnd('[time] FILTER.js');
        },
    };
})(jQuery, Drupal, drupalSettings);