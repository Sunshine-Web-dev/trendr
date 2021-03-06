jQuery(document).ready(function(e) {
    e('.cur_page').val('2');
    load_more_click();
    e('#adminbar-search').focusin(function() {
        e(this).css('width', '200px');
        e(this).css('background-color', 'rgb(255,255,255,1)');
    });
    e('#adminbar-search').focusout(function() {
        e(this).css('width', '24px');
        e(this).css('background-color', 'rgb(255,255,255,0)');
    });

    function load_more_click() {
        e('.load_more').click(function() {
            e('.load_more').addClass('loading');
            e.post(ajaxurl, {
                action: BBOSS_GLOBAL_SEARCH.action,
                nonce: BBOSS_GLOBAL_SEARCH.nonce,
                subset: e('.active').attr("data-item"),
                s: BBOSS_GLOBAL_SEARCH.search_term,
                view: "content",
                type: 'load_more',
                list: e('.cur_page').val()
            }).done(function(t) {
                t = JSON.parse(t);
                if (t.content != '') {
                    if (e('.no_of_pages').val() == e('.cur_page').val()) {
                        e('.pagination').hide();
                    }
                    e('.cur_page').val(t.list);
                    e('.load_more').removeClass('loading');
                    e('.pagination').before(t.content);
                }
            })
        })
    }
    BBOSS_GLOBAL_SEARCH.cache = [];
    e("form[action='" + BBOSS_GLOBAL_SEARCH.search_url + "']").each(function() {
        var t = e(this);
        $search_field = t.find("input[name='s']");
        if ($search_field.length > 0) {
            currentType = "";
            e($search_field).autocomplete({
                source: function(t, n) {
                    var r = t.term;
                    if (r in BBOSS_GLOBAL_SEARCH.cache) {
                        n(BBOSS_GLOBAL_SEARCH.cache[r]);
                        return
                    }
                    var i = {
                        action: BBOSS_GLOBAL_SEARCH.action,
                        nonce: BBOSS_GLOBAL_SEARCH.nonce,
                        search_term: t.term
                    };
                    n({
                        value: '<div class="loading-msg"><span class="bb_global_search_spinner"></span>' + BBOSS_GLOBAL_SEARCH.loading_msg + "</div>"
                    });
                    e.ajax({
                        url: ajaxurl,
                        dataType: "json",
                        data: i,
                        success: function(e) {
                            BBOSS_GLOBAL_SEARCH.cache[r] = e;
                            n(e)
                        }
                    })
                },
                minLength: 2,
                select: function(t, n) {
                    window.location = e(n.item.value).find("a").attr("href");
                    return false
                },
                focus: function(t, n) {
                    e(".ui-autocomplete li").removeClass("ui-state-hover");
                    e(".ui-autocomplete").find("li:has(a.ui-state-focus)").addClass("ui-state-hover");
                    return false
                },
                open: function() {
                    e(".bb-global-search-ac").outerWidth(e(this).outerWidth())
                }
            }).data("ui-autocomplete")._renderItem = function(t, n) {
                t.addClass("bb-global-search-ac");
                if (n.type_label != "") {
                    e(t).data("current_cat", n.type);
                    return e("<li>").attr("class", "bbls-" + n.type + "-type bbls-category").append("<span>" + n.value + "</span>").appendTo(t)
                } else {
                    return e("<li>").attr("class", "bbls-" + n.type + "-type bbls-sub-item").append("<a class='x'>" + n.value + "</a>").appendTo(t)
                }
            }
        }
    });
    e(document).on("click", ".bboss_search_results_wrapper .contour-select li a", function(t) {
        t.preventDefault();
        _this = this;contour-select
        e(this).addClass("loading");
        get_page = e.post(ajaxurl, {
            action: BBOSS_GLOBAL_SEARCH.action,
            nonce: BBOSS_GLOBAL_SEARCH.nonce,
            subset: e(this).parent().data("item"),
            s: BBOSS_GLOBAL_SEARCH.search_term,
            view: "content"
        });
        get_page.done(function(t) {
            e(_this).removeClass("loading");
            t = JSON.parse(t);
            if (t.content != "") {
                present = e(".bboss_search_page");
                present.after(t.content);
                load_more_click();
                present.remove()
            }
        });
        get_page.fail(function() {
            e(_this).removeClass("loading")
        });
        return false
    });
    e(document).on("click", ".bboss_search_results_wrapper .pagination-links a", function(t) {
        t.preventDefault();
        _this = this;
        e(this).addClass("loading");
        var n = {
            action: BBOSS_GLOBAL_SEARCH.action,
            nonce: BBOSS_GLOBAL_SEARCH.nonce,
            subset: e(this).parent().data("item"),
            s: BBOSS_GLOBAL_SEARCH.search_term,
            view: "content",
            list: e(this).data("pagenumber")
        };
        var r = e(".bboss_search_results_wrapper .contour-selectli.active").data("item");
        n.subset = r;
        get_page = e.post(ajaxurl, n);
        get_page.done(function(t) {
            e(_this).removeClass("loading");
            if (t != "") {
                present = e(".bboss_search_page");
                present.after(t);
                present.remove()
            }
        });
        get_page.fail(function() {
            e(_this).removeClass("loading")
        });
        return false
    })
})