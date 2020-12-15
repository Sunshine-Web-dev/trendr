// ///////////////////////MEDIA FILE UPLOAD TO BE
// CHANGED////////////////////////////////////
/**
 * http://github.com/valums/file-uploader
 * 
 * Multiple file upload component with progress-bar, drag-and-drop. © 2010
 * Andrew Valums ( andrew(at)valums.com )
 * 
 * Licensed under GNU GPL 2 or later, see license.txt.
 */

 /*
    *Revised by Virginie LE GUEN BERTHEAUME
    * -- Removed --
        * 'getImageBase64URL' function
        * 'ImageResizer' handler
        * 'dataURItoBlob' function
    * -- Added --
        * 'loadImage' and 'loadImage-exif' - by BlueImp
*/
! function(e) {
    "use strict";

    function t(e, i, a) {
        var o, n = document.createElement("img");
        return n.onerror = function(o) {
            return t.onerror(n, o, e, i, a)
        }, n.onload = function(o) {
            return t.onload(n, o, e, i, a)
        }, "string" == typeof e ? (t.fetchBlob(e, function(i) {
            i ? (e = i, o = t.createObjectURL(e)) : (o = e, a && a.crossOrigin && (n.crossOrigin = a.crossOrigin)), n.src = o
        }, a), n) : t.isInstanceOf("Blob", e) || t.isInstanceOf("File", e) ? (o = n._objectURL = t.createObjectURL(e)) ? (n.src = o, n) : t.readFile(e, function(e) {
            var t = e.target;
            t && t.result ? n.src = t.result : i && i(e)
        }) : void 0
    }

    function i(e, i) {
        !e._objectURL || i && i.noRevoke || (t.revokeObjectURL(e._objectURL), delete e._objectURL)
    }
    var a = e.createObjectURL && e || e.URL && URL.revokeObjectURL && URL || e.webkitURL && webkitURL;
    t.fetchBlob = function(e, t, i) {
        t()
    }, t.isInstanceOf = function(e, t) {
        return Object.prototype.toString.call(t) === "[object " + e + "]"
    }, t.transform = function(e, t, i, a, o) {
        i(e, o)
    }, t.onerror = function(e, t, a, o, n) {
        i(e, n), o && o.call(e, t)
    }, t.onload = function(e, a, o, n, r) {
        i(e, r), n && t.transform(e, r, n, o, {})
    }, t.createObjectURL = function(e) {
        return !!a && a.createObjectURL(e)
    }, t.revokeObjectURL = function(e) {
        return !!a && a.revokeObjectURL(e)
    }, t.readFile = function(t, i, a) {
        if (e.FileReader) {
            var o = new FileReader;
            if (o.onload = o.onerror = i, a = a || "readAsDataURL", o[a]) return o[a](t), o
        }
        return !1
    }, "function" == typeof define && define.amd ? define(function() {
        return t
    }) : "object" == typeof module && module.exports ? module.exports = t : e.loadImage = t
}("undefined" != typeof window && window || this),
function(e) {
    "use strict";
    "function" == typeof define && define.amd ? define(["./load-image"], e) : e("object" == typeof module && module.exports ? require("./load-image") : window.loadImage)
}(function(e) {
    "use strict";
    var t = e.transform;
    e.transform = function(i, a, o, n, r) {
        t.call(e, e.scale(i, a, r), a, o, n, r)
    }, e.transformCoordinates = function() {}, e.getTransformedOptions = function(e, t) {
        var i, a, o, n, r = t.aspectRatio;
        if (!r) return t;
        i = {};
        for (a in t) t.hasOwnProperty(a) && (i[a] = t[a]);
        return i.crop = !0, o = e.naturalWidth || e.width, n = e.naturalHeight || e.height, o / n > r ? (i.maxWidth = n * r, i.maxHeight = n) : (i.maxWidth = o, i.maxHeight = o / r), i
    }, e.renderImageToCanvas = function(e, t, i, a, o, n, r, s, l, d) {
        return e.getContext("2d").drawImage(t, i, a, o, n, r, s, l, d), e
    }, e.hasCanvasOption = function(e) {
        return e.canvas || e.crop || !!e.aspectRatio
    }, e.scale = function(t, i, a) {
        function o() {
            var e = Math.max((l || v) / v, (d || P) / P);
            e > 1 && (v *= e, P *= e)
        }

        function n() {
            var e = Math.min((r || v) / v, (s || P) / P);
            e < 1 && (v *= e, P *= e)
        }
        i = i || {};
        var r, s, l, d, c, u, f, g, h, m, p, S = document.createElement("canvas"),
            b = t.getContext || e.hasCanvasOption(i) && S.getContext,
            y = t.naturalWidth || t.width,
            x = t.naturalHeight || t.height,
            v = y,
            P = x;
        if (b && (f = (i = e.getTransformedOptions(t, i, a)).left || 0, g = i.top || 0, i.sourceWidth ? (c = i.sourceWidth, void 0 !== i.right && void 0 === i.left && (f = y - c - i.right)) : c = y - f - (i.right || 0), i.sourceHeight ? (u = i.sourceHeight, void 0 !== i.bottom && void 0 === i.top && (g = x - u - i.bottom)) : u = x - g - (i.bottom || 0), v = c, P = u), r = i.maxWidth, s = i.maxHeight, l = i.minWidth, d = i.minHeight, b && r && s && i.crop ? (v = r, P = s, (p = c / u - r / s) < 0 ? (u = s * c / r, void 0 === i.top && void 0 === i.bottom && (g = (x - u) / 2)) : p > 0 && (c = r * u / s, void 0 === i.left && void 0 === i.right && (f = (y - c) / 2))) : ((i.contain || i.cover) && (l = r = r || l, d = s = s || d), i.cover ? (n(), o()) : (o(), n())), b) {
            if ((h = i.pixelRatio) > 1 && (S.style.width = v + "px", S.style.height = P + "px", v *= h, P *= h, S.getContext("2d").scale(h, h)), (m = i.downsamplingRatio) > 0 && m < 1 && v < c && P < u)
                for (; c * m > v;) S.width = c * m, S.height = u * m, e.renderImageToCanvas(S, t, f, g, c, u, 0, 0, S.width, S.height), f = 0, g = 0, c = S.width, u = S.height, (t = document.createElement("canvas")).width = c, t.height = u, e.renderImageToCanvas(t, S, 0, 0, c, u, 0, 0, c, u);
            return S.width = v, S.height = P, e.transformCoordinates(S, i), e.renderImageToCanvas(S, t, f, g, c, u, 0, 0, v, P)
        }
        return t.width = v, t.height = P, t
    }
}),
function(e) {
    "use strict";
    "function" == typeof define && define.amd ? define(["./load-image"], e) : e("object" == typeof module && module.exports ? require("./load-image") : window.loadImage)
}(function(e) {
    "use strict";
    var t = "undefined" != typeof Blob && (Blob.prototype.slice || Blob.prototype.webkitSlice || Blob.prototype.mozSlice);
    e.blobSlice = t && function() {
        return (this.slice || this.webkitSlice || this.mozSlice).apply(this, arguments)
    }, e.metaDataParsers = {
        jpeg: {
            65505: []
        }
    }, e.parseMetaData = function(t, i, a, o) {
        a = a || {}, o = o || {};
        var n = this,
            r = a.maxMetaDataSize || 262144;
        !!("undefined" != typeof DataView && t && t.size >= 12 && "image/jpeg" === t.type && e.blobSlice) && e.readFile(e.blobSlice.call(t, 0, r), function(t) {
            if (t.target.error) return console.log(t.target.error), void i(o);
            var r, s, l, d, c = t.target.result,
                u = new DataView(c),
                f = 2,
                g = u.byteLength - 4,
                h = f;
            if (65496 === u.getUint16(0)) {
                for (; f < g && ((r = u.getUint16(f)) >= 65504 && r <= 65519 || 65534 === r);) {
                    if (s = u.getUint16(f + 2) + 2, f + s > u.byteLength) {
                        console.log("Invalid meta data: Invalid segment size.");
                        break
                    }
                    if (l = e.metaDataParsers.jpeg[r])
                        for (d = 0; d < l.length; d += 1) l[d].call(n, u, f, s, o, a);
                    h = f += s
                }!a.disableImageHead && h > 6 && (c.slice ? o.imageHead = c.slice(0, h) : o.imageHead = new Uint8Array(c).subarray(0, h))
            } else console.log("Invalid JPEG file: Missing JPEG marker.");
            i(o)
        }, "readAsArrayBuffer") || i(o)
    }, e.hasMetaOption = function(e) {
        return e && e.meta
    };
    var i = e.transform;
    e.transform = function(t, a, o, n, r) {
        e.hasMetaOption(a) ? e.parseMetaData(n, function(r) {
            i.call(e, t, a, o, n, r)
        }, a, r) : i.apply(e, arguments)
    }
}),
function(e) {
    "use strict";
    "function" == typeof define && define.amd ? define(["./load-image", "./load-image-meta"], e) : "object" == typeof module && module.exports ? e(require("./load-image"), require("./load-image-meta")) : e(window.loadImage)
}(function(e) {
    "use strict";
    "undefined" != typeof fetch && "undefined" != typeof Request && (e.fetchBlob = function(t, i, a) {
        if (e.hasMetaOption(a)) return fetch(new Request(t, a)).then(function(e) {
            return e.blob()
        }).then(i).catch(function(e) {
            console.log(e), i()
        });
        i()
    })
}),
function(e) {
    "use strict";
    "function" == typeof define && define.amd ? define(["./load-image", "./load-image-meta"], e) : "object" == typeof module && module.exports ? e(require("./load-image"), require("./load-image-meta")) : e(window.loadImage)
}(function(e) {
    "use strict";
    e.ExifMap = function() {
        return this
    }, e.ExifMap.prototype.map = {
        Orientation: 274
    }, e.ExifMap.prototype.get = function(e) {
        return this[e] || this[this.map[e]]
    }, e.getExifThumbnail = function(e, t, i) {
        var a, o, n; {
            if (i && !(t + i > e.byteLength)) {
                for (a = [], o = 0; o < i; o += 1) n = e.getUint8(t + o), a.push((n < 16 ? "0" : "") + n.toString(16));
                return "data:image/jpeg,%" + a.join("%")
            }
            console.log("Invalid Exif data: Invalid thumbnail data.")
        }
    }, e.exifTagTypes = {
        1: {
            getValue: function(e, t) {
                return e.getUint8(t)
            },
            size: 1
        },
        2: {
            getValue: function(e, t) {
                return String.fromCharCode(e.getUint8(t))
            },
            size: 1,
            ascii: !0
        },
        3: {
            getValue: function(e, t, i) {
                return e.getUint16(t, i)
            },
            size: 2
        },
        4: {
            getValue: function(e, t, i) {
                return e.getUint32(t, i)
            },
            size: 4
        },
        5: {
            getValue: function(e, t, i) {
                return e.getUint32(t, i) / e.getUint32(t + 4, i)
            },
            size: 8
        },
        9: {
            getValue: function(e, t, i) {
                return e.getInt32(t, i)
            },
            size: 4
        },
        10: {
            getValue: function(e, t, i) {
                return e.getInt32(t, i) / e.getInt32(t + 4, i)
            },
            size: 8
        }
    }, e.exifTagTypes[7] = e.exifTagTypes[1], e.getExifValue = function(t, i, a, o, n, r) {
        var s, l, d, c, u, f, g = e.exifTagTypes[o];
        if (g) {
            if (s = g.size * n, !((l = s > 4 ? i + t.getUint32(a + 8, r) : a + 8) + s > t.byteLength)) {
                if (1 === n) return g.getValue(t, l, r);
                for (d = [], c = 0; c < n; c += 1) d[c] = g.getValue(t, l + c * g.size, r);
                if (g.ascii) {
                    for (u = "", c = 0; c < d.length && "\0" !== (f = d[c]); c += 1) u += f;
                    return u
                }
                return d
            }
            console.log("Invalid Exif data: Invalid data offset.")
        } else console.log("Invalid Exif data: Invalid tag type.")
    }, e.parseExifTag = function(t, i, a, o, n) {
        var r = t.getUint16(a, o);
        n.exif[r] = e.getExifValue(t, i, a, t.getUint16(a + 2, o), t.getUint32(a + 4, o), o)
    }, e.parseExifTags = function(e, t, i, a, o) {
        var n, r, s;
        if (i + 6 > e.byteLength) console.log("Invalid Exif data: Invalid directory offset.");
        else {
            if (n = e.getUint16(i, a), !((r = i + 2 + 12 * n) + 4 > e.byteLength)) {
                for (s = 0; s < n; s += 1) this.parseExifTag(e, t, i + 2 + 12 * s, a, o);
                return e.getUint32(r, a)
            }
            console.log("Invalid Exif data: Invalid directory size.")
        }
    }, e.parseExifData = function(t, i, a, o, n) {
        if (!n.disableExif) {
            var r, s, l, d = i + 10;
            if (1165519206 === t.getUint32(i + 4))
                if (d + 8 > t.byteLength) console.log("Invalid Exif data: Invalid segment size.");
                else if (0 === t.getUint16(i + 8)) {
                switch (t.getUint16(d)) {
                    case 18761:
                        r = !0;
                        break;
                    case 19789:
                        r = !1;
                        break;
                    default:
                        return void console.log("Invalid Exif data: Invalid byte alignment marker.")
                }
                42 === t.getUint16(d + 2, r) ? (s = t.getUint32(d + 4, r), o.exif = new e.ExifMap, (s = e.parseExifTags(t, d, d + s, r, o)) && !n.disableExifThumbnail && (l = {
                    exif: {}
                }, s = e.parseExifTags(t, d, d + s, r, l), l.exif[513] && (o.exif.Thumbnail = e.getExifThumbnail(t, d + l.exif[513], l.exif[514]))), o.exif[34665] && !n.disableExifSub && e.parseExifTags(t, d, d + o.exif[34665], r, o), o.exif[34853] && !n.disableExifGps && e.parseExifTags(t, d, d + o.exif[34853], r, o)) : console.log("Invalid Exif data: Missing TIFF marker.")
            } else console.log("Invalid Exif data: Missing byte alignment offset.")
        }
    }, e.metaDataParsers.jpeg[65505].push(e.parseExifData)
}),
function(e) {
    "use strict";
    "function" == typeof define && define.amd ? define(["./load-image", "./load-image-exif"], e) : "object" == typeof module && module.exports ? e(require("./load-image"), require("./load-image-exif")) : e(window.loadImage)
}(function(e) {
    "use strict";
    e.ExifMap.prototype.tags = {
        
            Orientation: {
                1: "top-left",
                2: "top-right",
                3: "bottom-right",
                4: "bottom-left",
                5: "left-top",
                6: "right-top",
                7: "right-bottom",
                8: "left-bottom"
            }
        }, e.ExifMap.prototype.getText = function(e) {
            var t = this.get(e);
            switch (e) {
              
                case "Orientation":
                    return this.stringValues[e][t];

            }
            return String(t)
        },
        function(e) {
            var t, i = e.tags,
                a = e.map;
            for (t in i) i.hasOwnProperty(t) && (a[i[t]] = t)
        }(e.ExifMap.prototype), e.ExifMap.prototype.getAll = function() {
            var e, t, i = {};
            for (e in this) this.hasOwnProperty(e) && (t = this.tags[e]) && (i[t] = this.getText(t));
            return i
        }
}),
function(e) {
    "use strict";
    "function" == typeof define && define.amd ? define(["./load-image", "./load-image-scale", "./load-image-meta"], e) : "object" == typeof module && module.exports ? e(require("./load-image"), require("./load-image-scale"), require("./load-image-meta")) : e(window.loadImage)
}(function(e) {
    "use strict";
    var t = e.hasCanvasOption,
        i = e.hasMetaOption,
        a = e.transformCoordinates,
        o = e.getTransformedOptions;
    e.hasCanvasOption = function(i) {
        return !!i.orientation || t.call(e, i)
    }, e.hasMetaOption = function(t) {
        return t && !0 === t.orientation || i.call(e, t)
    }, e.transformCoordinates = function(t, i) {
        a.call(e, t, i);
        var o = t.getContext("2d"),
            n = t.width,
            r = t.height,
            s = t.style.width,
            l = t.style.height,
            d = i.orientation;
        if (d && !(d > 8)) switch (d > 4 && (t.width = r, t.height = n, t.style.width = l, t.style.height = s), d) {
            case 2:
                o.translate(n, 0), o.scale(-1, 1);
                break;
            case 3:
                o.translate(n, r), o.rotate(Math.PI);
                break;
            case 4:
                o.translate(0, r), o.scale(1, -1);
                break;
            case 5:
                o.rotate(.5 * Math.PI), o.scale(1, -1);
                break;
            case 6:
                o.rotate(.5 * Math.PI), o.translate(0, -r);
                break;
            case 7:
                o.rotate(.5 * Math.PI), o.translate(n, -r), o.scale(-1, 1);
                break;
            case 8:
                o.rotate(-.5 * Math.PI), o.translate(-n, 0)
        }
    }, e.getTransformedOptions = function(t, i, a) {
        var n, r, s = o.call(e, t, i),
            l = s.orientation;
        if (!0 === l && a && a.exif && (l = a.exif.get("Orientation")), !l || l > 8 || 1 === l) return s;
        n = {};
        for (r in s) s.hasOwnProperty(r) && (n[r] = s[r]);
        switch (n.orientation = l, l) {
            case 2:
                n.left = s.right, n.right = s.left;
                break;
            case 3:
                n.left = s.right, n.top = s.bottom, n.right = s.left, n.bottom = s.top;
                break;
            case 4:
                n.top = s.bottom, n.bottom = s.top;
                break;
            case 5:
                n.left = s.top, n.top = s.left, n.right = s.bottom, n.bottom = s.right;
                break;
            case 6:
                n.left = s.top, n.top = s.right, n.right = s.bottom, n.bottom = s.left;
                break;
            case 7:
                n.left = s.bottom, n.top = s.right, n.right = s.top, n.bottom = s.left;
                break;
            case 8:
                n.left = s.bottom, n.top = s.left, n.right = s.top, n.bottom = s.right
        }
        return n.orientation > 4 && (n.maxWidth = s.maxHeight, n.maxHeight = s.maxWidth, n.minWidth = s.minHeight, n.minHeight = s.minWidth, n.sourceWidth = s.sourceHeight, n.sourceHeight = s.sourceWidth), n
    }
});
var qq = qq || {};

/**
 * Adds all missing properties from second obj to first obj
 */
qq.extend = function(first, second) {
    for ( var prop in second) {
        first[prop] = second[prop];
    }
};

/**
 * Searches for a given element in the array, returns -1 if it is not present.
 * 
 * @param {Number}
 *            [from] The index at which to begin the search
 */
qq.indexOf = function(arr, elt, from) {
    if (arr.indexOf)
        return arr.indexOf(elt, from);

    from = from || 0;
    var len = arr.length;

    if (from < 0)
        from += len;

    for (; from < len; from++) {
        if (from in arr && arr[from] === elt) {
            return from;
        }
    }
    return -1;
};

qq.getUniqueId = (function() {
    var id = 0;
    return function() {
        return id++;
    };
})();

//
// Events

qq.attach = function(element, type, fn) {
    if (element.addEventListener) {
        element.addEventListener(type, fn, false);
    } else if (element.attachEvent) {
        element.attachEvent('on' + type, fn);
    }
};
qq.detach = function(element, type, fn) {
    if (element.removeEventListener) {
        element.removeEventListener(type, fn, false);
    } else if (element.attachEvent) {
        element.detachEvent('on' + type, fn);
    }
};

qq.preventDefault = function(e) {
    if (e.preventDefault) {
        e.preventDefault();
    } else {
        e.returnValue = false;
    }
};

//
// Node manipulations

/**
 * Insert node a before node b.
 */
qq.insertBefore = function(a, b) {
    b.parentNode.insertBefore(a, b);
};
qq.remove = function(element) {
    element.parentNode.removeChild(element);
};

qq.contains = function(parent, descendant) {
    // compareposition returns false in this case
    if (parent == descendant)
        return true;

    if (parent.contains) {
        return parent.contains(descendant);
    } else {
        return !!(descendant.compareDocumentPosition(parent) & 8);
    }
};

/**
 * Creates and returns element from html string Uses innerHTML to create an
 * element
 */
qq.toElement = (function() {
    var div = document.createElement('div');
    return function(html) {
        div.innerHTML = html;
        var element = div.firstChild;
        div.removeChild(element);
        return element;
    };
})();

//
// Node properties and attributes

/**
 * Sets styles for an element. Fixes opacity in IE6-8.
 */
qq.css = function(element, styles) {
    if (styles.opacity != null) {
        if (typeof element.style.opacity != 'string' && typeof (element.filters) != 'undefined') {
            styles.filter = 'alpha(opacity=' + Math.round(100 * styles.opacity) + ')';
        }
    }
    qq.extend(element.style, styles);
};
qq.hasClass = function(element, name) {
    var re = new RegExp('(^| )' + name + '( |$)');
    return re.test(element.className);
};
qq.addClass = function(element, name) {
    if (!qq.hasClass(element, name)) {
        element.className += ' ' + name;
    }
};
qq.removeClass = function(element, name) {
    var re = new RegExp('(^| )' + name + '( |$)');
    element.className = element.className.replace(re, ' ').replace(/^\s+|\s+$/g, "");
};
qq.setText = function(element, text) {
    element.innerText = text;
    element.textContent = text;
};

//
// Selecting elements

qq.children = function(element) {
    var children = [], child = element.firstChild;

    while (child) {
        if (child.nodeType == 1) {
            children.push(child);
        }
        child = child.nextSibling;
    }

    return children;
};

qq.getByClass = function(element, className) {
    if (element.querySelectorAll) {
        return element.querySelectorAll('.' + className);
    }

    var result = [];
    var candidates = element.getElementsByTagName("*");
    var len = candidates.length;

    for ( var i = 0; i < len; i++) {
        if (qq.hasClass(candidates[i], className)) {
            result.push(candidates[i]);
        }
    }
    return result;
};

/**
 * obj2url() takes a json-object as argument and generates a querystring. pretty
 * much like jQuery.param()
 * 
 * how to use:
 * 
 * `qq.obj2url({a:'b',c:'d'},'http://any.url/upload?otherParam=value');`
 * 
 * will result in:
 * 
 * `http://any.url/upload?otherParam=value&a=b&c=d`
 * 
 * @param Object
 *            JSON-Object
 * @param String
 *            current querystring-part
 * @return String encoded querystring
 */
qq.obj2url = function(obj, temp, prefixDone) {
    var uristrings = [], prefix = '&', add = function(nextObj, i) {
        var nextTemp = temp ? (/\[\]$/.test(temp)) // prevent double-encoding
        ? temp : temp + '[' + i + ']' : i;
        if ((nextTemp != 'undefined') && (i != 'undefined')) {
            uristrings.push((typeof nextObj === 'object') ? qq.obj2url(nextObj, nextTemp, true)
                    : (Object.prototype.toString.call(nextObj) === '[object Function]') ? encodeURIComponent(nextTemp) + '=' + encodeURIComponent(nextObj())
                            : encodeURIComponent(nextTemp) + '=' + encodeURIComponent(nextObj));
        }
    };

    if (!prefixDone && temp) {
        prefix = (/\?/.test(temp)) ? (/\?$/.test(temp)) ? '' : '&' : '?';
        uristrings.push(temp);
        uristrings.push(qq.obj2url(obj));
    } else if ((Object.prototype.toString.call(obj) === '[object Array]') && (typeof obj != 'undefined')) {
        // we wont use a for-in-loop on an array (performance)
        for ( var i = 0, len = obj.length; i < len; ++i) {
            add(obj[i], i);
        }
    } else if ((typeof obj != 'undefined') && (obj !== null) && (typeof obj === "object")) {
        // for anything else but a scalar, we will use for-in-loop
        for ( var i in obj) {
            add(obj[i], i);
        }
    } else {
        uristrings.push(encodeURIComponent(temp) + '=' + encodeURIComponent(obj));
    }

    return uristrings.join(prefix).replace(/^&/, '').replace(/%20/g, '+');
};

//
//
// Uploader Classes
//
//

var qq = qq || {};

/**
 * Creates upload button, validates upload, but doesn't create file list or dd.
 */
qq.FileUploaderBasic = function(o) {
    this._options = {
        // set to true to see the server response
        debug : false,
        action : '/server/upload',
        params : {},
        button : null,
        multiple : true,
        maxConnections : 3,
        // validation
        allowedExtensions : [],
        resize : false,
        maxwidth : null,
        sizeLimit : 0,
        minSizeLimit : 0,
        // events
        // return false to cancel submit
        onSubmit : function(id, fileName) {
        },
        onProgress : function(id, fileName, loaded, total) {
        },
        onComplete : function(id, fileName, responseJSON) {
        },
        onCancel : function(id, fileName) {
        },
        // messages
        messages : {
            typeError : "{file} has invalid extension. Only {extensions} are allowed.",
            sizeError : "{file} is too large, maximum file size is {sizeLimit}.",
            minSizeError : "{file} is too small, minimum file size is {minSizeLimit}.",
            emptyError : "{file} is empty, please select files again without it.",
            onLeave : "The files are being uploaded, if you leave now the upload will be cancelled."
        },
        showMessage : function(message) {
            alert(message);
        },
        scaling : {
            // send the original file as well
            sendOriginal : true,

            // fox orientation for scaled images
            orient : true,

            // If null, scaled image type will match reference image type. This
            // value will be referred to
            // for any size record that does not specific a type.
            defaultType : null,

            failureText : "Failed to scale",

            includeExif : false,

            // metadata about each requested scaled version
            sizes : []
        }
    };
    qq.extend(this._options, o);

    // number of files being uploaded
    this._filesInProgress = 0;
    this._handler = this._createUploadHandler();

    if (this._options.button) {
        this._button = this._createUploadButton(this._options.button);
    }

    this._preventLeaveInProgress();
    this._scaler = (qq.Scaler && new qq.Scaler(this._options.scaling, qq.bind(this.log, this))) || {};
    if (this._scaler.enabled) {
        this._customNewFileHandler = qq.bind(this._scaler.handleNewFile, this._scaler);
    }

};

qq.FileUploaderBasic.prototype = {
    setParams : function(params) {
        this._options.params = params;
    },
    getInProgress : function() {
        return this._filesInProgress;
    },
    _createUploadButton : function(element) {
        var self = this;

        return new qq.UploadButton({
            element : element,
            multiple : this._options.multiple && qq.UploadHandlerXhr.isSupported(),
            onChange : function(input) {
                self._onInputChange(input);
            }
        });
    },
    _createUploadHandler : function() {
        var self = this, handlerClass;

        if (qq.UploadHandlerXhr.isSupported()) {
            handlerClass = 'UploadHandlerXhr';
        } else {
            handlerClass = 'UploadHandlerForm';
        }

        var handler = new qq[handlerClass]({
            debug : this._options.debug,
            action : this._options.action,
            maxConnections : this._options.maxConnections,
            maxwidth : this._options.maxwidth,
            resize : this._options.resize,
            onProgress : function(id, fileName, loaded, total) {
                self._onProgress(id, fileName, loaded, total);
                self._options.onProgress(id, fileName, loaded, total);
            },
            onComplete : function(id, fileName, result) {
                self._onComplete(id, fileName, result);
                self._options.onComplete(id, fileName, result);
            },
            onCancel : function(id, fileName) {
                self._onCancel(id, fileName);
                self._options.onCancel(id, fileName);
            }
        });

        return handler;
    },
    _preventLeaveInProgress : function() {
        var self = this;

        qq.attach(window, 'beforeunload', function(e) {
            if (!self._filesInProgress) {
                return;
            }

            var e = e || window.event;
            // for ie, ff
            e.returnValue = self._options.messages.onLeave;
            // for webkit
            return self._options.messages.onLeave;
        });
    },
    _onSubmit : function(id, fileName) {
        this._filesInProgress++;
    },
    _onProgress : function(id, fileName, loaded, total) {
    },
    _onComplete : function(id, fileName, result) {
        this._filesInProgress--;
        if (result.error) {
            this._options.showMessage(result.error);
        }
    },
    _onCancel : function(id, fileName) {
        this._filesInProgress--;
    },
    _onInputChange : function(input) {
        if (this._handler instanceof qq.UploadHandlerXhr) {
            this._uploadFileList(input.files);
        } else {
            if (this._validateFile(input)) {
                this._uploadFile(input);
            }
        }
        this._button.reset();
    },
    _uploadFileList : function(files) {
        for ( var i = 0; i < files.length; i++) {
            if (!this._validateFile(files[i])) {
                return;
            }
        }

        for ( var i = 0; i < files.length; i++) {
            this._uploadFile(files[i]);
        }
    },
    _uploadFile : function(fileContainer) {
        var id = this._handler.add(fileContainer);
        var fileName = this._handler.getName(id);

        if (this._options.onSubmit(id, fileName) !== false) {
            this._onSubmit(id, fileName);
            this._handler.upload(id, this._options.params);
        }
    },
    _validateFile : function(file) {
        var name, size;

        if (file.value) {
            // it is a file input
            // get input value and remove path to normalize
            name = file.value.replace(/.*(\/|\\)/, "");
        } else {
            // fix missing properties in Safari
            name = file.fileName != null ? file.fileName : file.name;
            size = file.fileSize != null ? file.fileSize : file.size;
        }

        if (!this._isAllowedExtension(name)) {
            this._error('typeError', name);
            return false;

        } else if (size === 0) {
            this._error('emptyError', name);
            return false;

        } else if (size && this._options.sizeLimit && size > this._options.sizeLimit) {
            this._error('sizeError', name);
            return false;

        } else if (size && size < this._options.minSizeLimit) {
            this._error('minSizeError', name);
            return false;
        }

        return true;
    },
    _error : function(code, fileName) {
        var message = this._options.messages[code];
        function r(name, replacement) {
            message = message.replace(name, replacement);
        }

        r('{file}', this._formatFileName(fileName));
        r('{extensions}', this._options.allowedExtensions.join(', '));
        r('{sizeLimit}', this._formatSize(this._options.sizeLimit));
        r('{minSizeLimit}', this._formatSize(this._options.minSizeLimit));

        this._options.showMessage(message);
    },
    _formatFileName : function(name) {
        if (name.length > 33) {
            name = name.slice(0, 19) + '...' + name.slice(-13);
        }
        return name;
    },
    _isAllowedExtension : function(fileName) {
        var ext = (-1 !== fileName.indexOf('.')) ? fileName.replace(/.*[.]/, '').toLowerCase() : '';
        var allowed = this._options.allowedExtensions;

        if (!allowed.length) {
            return true;
        }

        for ( var i = 0; i < allowed.length; i++) {
            if (allowed[i].toLowerCase() == ext) {
                return true;
            }
        }

        return false;
    },
    _formatSize : function(bytes) {
        var i = -1;
        do {
            bytes = bytes / 1024;
            i++;
        } while (bytes > 99);

        return Math.max(bytes, 0.1).toFixed(1) + [
                'kB', 'MB', 'GB', 'TB', 'PB', 'EB'
        ][i];
    }
};

/**
 * Class that creates upload widget with drag-and-drop and file list
 * 
 * @inherits qq.FileUploaderBasic
 */
qq.FileUploader = function(o) {
    // call parent constructor
    qq.FileUploaderBasic.apply(this, arguments);

    // additional options
    qq.extend(this._options, {
        element : null,
        // if set, will be used instead of qq-upload-list in template
        listElement : null,

        template : '<div class="qq-uploader">' + '<div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>'
                + '<div class="qq-upload-button">Upload a file</div>' + '<ul class="qq-upload-list"></ul>' + '</div>',

        // template for one item in file list
        fileTemplate : '<li>' + '<span class="qq-upload-file"></span>' + '<span class="qq-upload-spinner"></span>'
                + '<a class="qq-upload-cancel" href="#">Cancel</a>' + '<span class="qq-upload-failed-text">Failed</span>' + '</li>',

        classes : {
            // used to get elements from templates
            button : 'qq-upload-button',
            drop : 'qq-upload-drop-area',
            dropActive : 'qq-upload-drop-area-active',
            list : 'qq-upload-list',

            file : 'qq-upload-file',
            spinner : 'qq-upload-spinner',
            // size : 'qq-upload-size',
            cancel : 'qq-upload-cancel',

            // added to list item when upload completes
            // used in css to hide progress spinner
            success : 'qq-upload-success',
            fail : 'qq-upload-fail'
        }
    });
    // overwrite options with user supplied
    qq.extend(this._options, o);

    this._element = this._options.element;
    this._element.innerHTML = this._options.template;
    this._listElement = this._options.listElement || this._find(this._element, 'list');

    this._classes = this._options.classes;

    this._button = this._createUploadButton(this._find(this._element, 'button'));

    this._bindCancelEvent();
    this._setupDragDrop();
};

// inherit from Basic Uploader
qq.extend(qq.FileUploader.prototype, qq.FileUploaderBasic.prototype);

qq.extend(qq.FileUploader.prototype, {
    /**
     * Gets one of the elements listed in this._options.classes
     */
    _find : function(parent, type) {
        var element = qq.getByClass(parent, this._options.classes[type])[0];
        if (!element) {
            // throw new Error('element not found ' + type);
        }

        return element;
    },
    _setupDragDrop : function() {
        var self = this, dropArea = this._find(this._element, 'drop');

        var dz = new qq.UploadDropZone({
            element : dropArea,
            onEnter : function(e) {
                qq.addClass(dropArea, self._classes.dropActive);
                e.stopPropagation();
            },
            onLeave : function(e) {
                e.stopPropagation();
            },
            onLeaveNotDescendants : function(e) {
                qq.removeClass(dropArea, self._classes.dropActive);
            },
            onDrop : function(e) {
                dropArea.style.display = 'none';
                qq.removeClass(dropArea, self._classes.dropActive);
                self._uploadFileList(e.dataTransfer.files);
            }
        });

        dropArea.style.display = 'none';

        qq.attach(document, 'dragenter', function(e) {
            if (!dz._isValidFileDrag(e))
                return;

            dropArea.style.display = 'block';
        });
        qq.attach(document, 'dragleave', function(e) {
            if (!dz._isValidFileDrag(e))
                return;

            var relatedTarget = document.elementFromPoint(e.clientX, e.clientY);
            // only fire when leaving document out
            if (!relatedTarget || relatedTarget.nodeName == "HTML") {
                dropArea.style.display = 'none';
            }
        });
    },
    _onSubmit : function(id, fileName) {
        qq.FileUploaderBasic.prototype._onSubmit.apply(this, arguments);
        this._addToList(id, fileName);
    },
    _onProgress : function(id, fileName, loaded, total) {
        qq.FileUploaderBasic.prototype._onProgress.apply(this, arguments);

        var item = this._getItemByFileId(id);
        var size = this._find(item, 'size');
        if (size) {
            size.style.display = 'inline';
            var text;
            if (loaded != total) {
                text = Math.round(loaded / total * 100) + '% from ' + this._formatSize(total);
            } else {
                text = this._formatSize(total);
            }

            qq.setText(size, text);
        }

    },
    _onComplete : function(id, fileName, result) {
        qq.FileUploaderBasic.prototype._onComplete.apply(this, arguments);

        // mark completed
        var item = this._getItemByFileId(id);
        qq.remove(this._find(item, 'cancel'));
        qq.remove(this._find(item, 'spinner'));

        if (result.success) {
            qq.addClass(item, this._classes.success);
        } else {
            qq.addClass(item, this._classes.fail);
        }
    },
    _addToList : function(id, fileName) {
        var item = qq.toElement(this._options.fileTemplate);
        item.qqFileId = id;

        var fileElement = this._find(item, 'file');

        qq.setText(fileElement, this._formatFileName(fileName));

        var sizeElement = this._find(item, 'size');
        if (sizeElement)
            sizeElement.style.display = 'none';

        this._listElement.appendChild(item);
    },
    _getItemByFileId : function(id) {
        var item = this._listElement.firstChild;

        // there can't be txt nodes in dynamically created list
        // and we can use nextSibling
        while (item) {
            if (item.qqFileId == id)
                return item;
            item = item.nextSibling;
        }
    },
    /**
     * delegate click event for cancel link
     */
    _bindCancelEvent : function() {
        var self = this, list = this._listElement;

        qq.attach(list, 'click', function(e) {
            e = e || window.event;
            var target = e.target || e.srcElement;

            if (qq.hasClass(target, self._classes.cancel)) {
                qq.preventDefault(e);

                var item = target.parentNode;
                self._handler.cancel(item.qqFileId);
                qq.remove(item);
            }
        });
    }
});

qq.UploadDropZone = function(o) {
    this._options = {
        element : null,
        onEnter : function(e) {
        },
        onLeave : function(e) {
        },
        // is not fired when leaving element by hovering descendants
        onLeaveNotDescendants : function(e) {
        },
        onDrop : function(e) {
        }
    };
    qq.extend(this._options, o);

    this._element = this._options.element;

    this._disableDropOutside();
    this._attachEvents();
};

qq.UploadDropZone.prototype = {
    _disableDropOutside : function(e) {
        // run only once for all instances
        if (!qq.UploadDropZone.dropOutsideDisabled) {

            qq.attach(document, 'dragover', function(e) {
                if (e.dataTransfer) {
                    e.dataTransfer.dropEffect = 'none';
                    e.preventDefault();
                }
            });

            qq.UploadDropZone.dropOutsideDisabled = true;
        }
    },
    _attachEvents : function() {
        var self = this;

        qq.attach(self._element, 'dragover', function(e) {
            if (!self._isValidFileDrag(e))
                return;

            var effect = e.dataTransfer.effectAllowed;
            if (effect == 'move' || effect == 'linkMove') {
                e.dataTransfer.dropEffect = 'move'; // for FF (only move
                // allowed)
            } else {
                e.dataTransfer.dropEffect = 'copy'; // for Chrome
            }

            e.stopPropagation();
            e.preventDefault();
        });

        qq.attach(self._element, 'dragenter', function(e) {
            if (!self._isValidFileDrag(e))
                return;

            self._options.onEnter(e);
        });

        qq.attach(self._element, 'dragleave', function(e) {
            if (!self._isValidFileDrag(e))
                return;

            self._options.onLeave(e);

            var relatedTarget = document.elementFromPoint(e.clientX, e.clientY);
            // do not fire when moving a mouse over a descendant
            if (qq.contains(this, relatedTarget))
                return;

            self._options.onLeaveNotDescendants(e);
        });

        qq.attach(self._element, 'drop', function(e) {
            if (!self._isValidFileDrag(e))
                return;

            e.preventDefault();
            self._options.onDrop(e);
        });
    },
    _isValidFileDrag : function(e) {
        var dt = e.dataTransfer,
        // do not check dt.types.contains in webkit, because it crashes safari 4
        isWebkit = navigator.userAgent.indexOf("AppleWebKit") > -1;

        // dt.effectAllowed is none in Safari 5
        // dt.types.contains check is for firefox
        return dt && dt.effectAllowed != 'none' && (dt.files || (!isWebkit && dt.types.contains && dt.types.contains('Files')));

    }
};

qq.UploadButton = function(o) {
    this._options = {
        element : null,
        // if set to true adds multiple attribute to file input
        multiple : false,
        // name attribute of file input
        name : 'file',
        onChange : function(input) {
        },
        hoverClass : 'qq-upload-button-hover',
        focusClass : 'qq-upload-button-focus'
    };

    qq.extend(this._options, o);

    this._element = this._options.element;

    // make button suitable container for input
    qq.css(this._element, {
        position : 'relative',
        overflow : 'hidden',
        // Make sure browse button is in the right side
        // in Internet Explorer
        direction : 'ltr'
    });

    this._input = this._createInput();
};

qq.UploadButton.prototype = {
    /* returns file input element */
    getInput : function() {
        return this._input;
    },
    /* cleans/recreates the file input */
    reset : function() {
        if (this._input.parentNode) {
            qq.remove(this._input);
        }

        qq.removeClass(this._element, this._options.focusClass);
        this._input = this._createInput();
    },
    _createInput : function() {
        var input = document.createElement("input");

        if (this._options.multiple) {
            input.setAttribute("multiple", "multiple");
        }

        input.setAttribute("type", "file");
        input.setAttribute("name", this._options.name);

        qq.css(input, {
            position : 'absolute',
            // in Opera only 'browse' button
            // is clickable and it is located at
            // the right side of the input
            right : 0,
            top : 0,
            fontFamily : 'Arial',
            // 4 persons reported this, the max values that worked for them were
            // 243, 236, 236, 118
            fontSize : '118px',
            margin : 0,
            padding : 0,
            cursor : 'pointer',
            opacity : 0
        });

        this._element.appendChild(input);

        var self = this;
        qq.attach(input, 'change', function() {
            self._options.onChange(input);
        });

        qq.attach(input, 'mouseover', function() {
            qq.addClass(self._element, self._options.hoverClass);
        });
        qq.attach(input, 'mouseout', function() {
            qq.removeClass(self._element, self._options.hoverClass);
        });
        qq.attach(input, 'focus', function() {
            qq.addClass(self._element, self._options.focusClass);
        });
        qq.attach(input, 'blur', function() {
            qq.removeClass(self._element, self._options.focusClass);
        });

        // IE and Opera, unfortunately have 2 tab stops on file input
        // which is unacceptable in our case, disable keyboard access
        if (window.attachEvent) {
            // it is IE or Opera
            input.setAttribute('tabIndex', "-1");
        }

        return input;
    }
};

/**
 * Class for uploading files, uploading itself is handled by child classes
 */
qq.UploadHandlerAbstract = function(o) {
    this._options = {
        debug : false,
        action : '/upload.php',
        // maximum number of concurrent uploads
        maxConnections : 999,
        onProgress : function(id, fileName, loaded, total) {
        },
        onComplete : function(id, fileName, response) {
        },
        onCancel : function(id, fileName) {
        }
    };
    qq.extend(this._options, o);

    this._queue = [];
    // params for files in queue
    this._params = [];
};
qq.UploadHandlerAbstract.prototype = {
    log : function(str) {
        if (this._options.debug && window.console)
            console.log('[uploader] ' + str);
    },
    /**
     * Adds file or file input to the queue
     * 
     * @returns id
     */
    add : function(file) {
    },
    /**
     * Sends the file identified by id and additional query params to the server
     */
    upload : function(id, params) {
        var len = this._queue.push(id);

        var copy = {};
        qq.extend(copy, params);
        this._params[id] = copy;

        // if too many active uploads, wait...
        if (len <= this._options.maxConnections) {
            this._upload(id, this._params[id]);
        }
    },
    /**
     * Cancels file upload by id
     */
    cancel : function(id) {
        this._cancel(id);
        this._dequeue(id);
    },
    /**
     * Cancells all uploads
     */
    cancelAll : function() {
        for ( var i = 0; i < this._queue.length; i++) {
            this._cancel(this._queue[i]);
        }
        this._queue = [];
    },
    /**
     * Returns name of the file identified by id
     */
    getName : function(id) {
    },
    /**
     * Returns size of the file identified by id
     */
    getSize : function(id) {
    },
    /**
     * Returns id of files being uploaded or waiting for their turn
     */
    getQueue : function() {
        return this._queue;
    },
    /**
     * Actual upload method
     */
    _upload : function(id) {
    },
    /**
     * Actual cancel method
     */
    _cancel : function(id) {
    },
    /**
     * Removes element from queue, starts upload of next
     */
    _dequeue : function(id) {
        var i = qq.indexOf(this._queue, id);
        this._queue.splice(i, 1);

        var max = this._options.maxConnections;

        if (this._queue.length >= max) {
            var nextId = this._queue[max - 1];
            this._upload(nextId, this._params[nextId]);
        }
    }
};

/**
 * Class for uploading files using form and iframe
 * 
 * @inherits qq.UploadHandlerAbstract
 */
qq.UploadHandlerForm = function(o) {
    qq.UploadHandlerAbstract.apply(this, arguments);

    this._inputs = {};
};
// @inherits qq.UploadHandlerAbstract
qq.extend(qq.UploadHandlerForm.prototype, qq.UploadHandlerAbstract.prototype);

qq.extend(qq.UploadHandlerForm.prototype, {
    add : function(fileInput) {
        fileInput.setAttribute('name', 'qqfile');
        var id = 'qq-upload-handler-iframe' + qq.getUniqueId();

        this._inputs[id] = fileInput;

        // remove file input from DOM
        if (fileInput.parentNode) {
            qq.remove(fileInput);
        }

        return id;
    },
    getName : function(id) {
        // get input value and remove path to normalize
        return this._inputs[id].value.replace(/.*(\/|\\)/, "");
    },
    _cancel : function(id) {
        this._options.onCancel(id, this.getName(id));

        delete this._inputs[id];

        var iframe = document.getElementById(id);
        if (iframe) {
            // to cancel request set src to something else
            // we use src="javascript:false;" because it doesn't
            // trigger ie6 prompt on https
            iframe.setAttribute('src', 'javascript:false;');

            qq.remove(iframe);
        }
    },
    _upload : function(id, params) {
        var input = this._inputs[id];

        if (!input) {
            throw new Error('file with passed id was not added, or already uploaded or cancelled');
        }

        var fileName = this.getName(id);

        var iframe = this._createIframe(id);
        var form = this._createForm(iframe, params);
        form.appendChild(input);

        var self = this;
        this._attachLoadEvent(iframe, function() {
            self.log('iframe loaded');

            var response = self._getIframeContentJSON(iframe);

            self._options.onComplete(id, fileName, response);
            self._dequeue(id);

            delete self._inputs[id];
            // timeout added to fix busy state in FF3.6
            setTimeout(function() {
                qq.remove(iframe);
            }, 1);
        });

        form.submit();
        qq.remove(form);

        return id;
    },
    _attachLoadEvent : function(iframe, callback) {
        qq.attach(iframe, 'load', function() {
            // when we remove iframe from dom
            // the request stops, but in IE load
            // event fires
            if (!iframe.parentNode) {
                return;
            }

            // fixing Opera 10.53
            if (iframe.contentDocument && iframe.contentDocument.body && iframe.contentDocument.body.innerHTML == "false") {
                // In Opera event is fired second time
                // when body.innerHTML changed from false
                // to server response approx. after 1 sec
                // when we upload file with iframe
                return;
            }

            callback();
        });
    },
    /**
     * Returns json object received by iframe from server.
     */
    _getIframeContentJSON : function(iframe) {
        // iframe.contentWindow.document - for IE<7
        var doc = iframe.contentDocument ? iframe.contentDocument : iframe.contentWindow.document, response;

        this.log("converting iframe's innerHTML to JSON");
        this.log("innerHTML = " + doc.body.innerHTML);

        try {
            response = eval("(" + doc.body.innerHTML + ")");
        } catch (err) {
            response = {};
        }

        return response;
    },
    /**
     * Creates iframe with unique name
     */
    _createIframe : function(id) {
        // We can't use following code as the name attribute
        // won't be properly registered in IE6, and new window
        // on form submit will open
        // var iframe = document.createElement('iframe');
        // iframe.setAttribute('name', id);

        var iframe = qq.toElement('<iframe src="javascript:false;" name="' + id + '" />');
        // src="javascript:false;" removes ie6 prompt on https

        iframe.setAttribute('id', id);

        iframe.style.display = 'none';
        document.body.appendChild(iframe);

        return iframe;
    },
    /**
     * Creates form, that will be submitted to iframe
     */
    _createForm : function(iframe, params) {
        // We can't use the following code in IE6
        // var form = document.createElement('form');
        // form.setAttribute('method', 'post');
        // form.setAttribute('enctype', 'multipart/form-data');
        // Because in this case file won't be attached to request
        var form = qq.toElement('<form method="post" enctype="multipart/form-data"></form>');

        var queryString = qq.obj2url(params, this._options.action);

        form.setAttribute('action', queryString);
        form.setAttribute('target', iframe.name);
        form.style.display = 'none';
        document.body.appendChild(form);

        return form;
    }
});

/**
 * Class for uploading files using xhr
 * 
 * @inherits qq.UploadHandlerAbstract
 */
qq.UploadHandlerXhr = function(o) {
    qq.UploadHandlerAbstract.apply(this, arguments);

    this._files = [];
    this._xhrs = [];

    // current loaded size in bytes for each file
    this._loaded = [];
};

// static method
qq.UploadHandlerXhr.isSupported = function() {
    var input = document.createElement('input');
    input.type = 'file';

    return ('multiple' in input && typeof File != "undefined" && typeof (new XMLHttpRequest()).upload != "undefined");
};

// @inherits qq.UploadHandlerAbstract
qq.extend(qq.UploadHandlerXhr.prototype, qq.UploadHandlerAbstract.prototype)

qq.extend(qq.UploadHandlerXhr.prototype, {
    /**
     * Adds file to the queue Returns id to use with upload, cancel
     */
    add : function(file) {
        if (!(file instanceof File)) {
            throw new Error('Passed obj in not a File (in qq.UploadHandlerXhr)');
        }

        return this._files.push(file) - 1;
    },
    getName : function(id) {
        var file = this._files[id];
        // fix missing name in Safari 4
        return file.fileName != null ? file.fileName : file.name;
    },
    getSize : function(id) {
        var file = this._files[id];
        return file.fileSize != null ? file.fileSize : file.size;
    },
    /**
     * Returns uploaded bytes for file identified by id
     */
    getLoaded : function(id) {
        return this._loaded[id] || 0;
    },
    /**
     * Sends the file identified by id and additional query params to the server
     * 
     * @param {Object}
     *            params name-value string pairs
     */
    _upload : function(id, params) {

        var file = this._files[id], name = this.getName(id), size = this.getSize(id);

        this._loaded[id] = 0;
        // Ensure it's an image
        if (file.type.match(/image.*/) && this._options.resize /*&& ((typeof FileReader)!='undefined')*/) {
            // console.log('An image has been loaded');

            var that = this;
            var pself = self;

            var whenImageLoaded = function( dataUrl ) {                
                that._loaded[id] = 0;

                // build query string
                params = params || {};
                params['filename'] = name;
                params['dataurl'] = dataUrl;

                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(e) {
                    if (e.lengthComputable) {
                        that._loaded[id] = e.loaded;
                        that._options.onProgress(id, name, e.loaded, e.total);
                    }
                }, false);

                xhr.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        // Do something with download progress
                    }
                }, false);

                $.ajax({
                    contentType : 'multipart/form-data',
                    xhr : function() {
                        return xhr;
                    },
                    type : 'POST',
                    url : that._options.action,
                    data : params,
                    complete : function(data) {
                        that._onComplete(id, xhr);
                    }
                });
            };            
            loadImage( file , function( img, data ) {
                var imgData = '';
                try {
                    imgData = img.toDataURL("image/jpeg" ,0.89 );
                } catch( e ) {
                    var drawImage=function(t,e,a,r,h,s){if(!/^[1-8]$/.test(e))throw new Error("orientation should be [1-8]");null==a&&(a=0),null==r&&(r=0),null==h&&(h=t.width),null==s&&(s=t.height);var l=document.createElement("canvas"),n=l.getContext("2d");switch(l.width=h,l.height=s,n.save(),+e){case 1:break;case 2:n.translate(h,0),n.scale(-1,1);break;case 3:n.translate(h,s),n.rotate(1*Math.PI);break;case 4:n.translate(0,s),n.scale(1,-1);break;case 5:l.width=s,l.height=h,n.rotate(.5*Math.PI),n.scale(1,-1);break;case 6:l.width=s,l.height=h,n.rotate(.5*Math.PI),n.translate(0,-s);break;case 7:l.width=s,l.height=h,n.rotate(1.5*Math.PI),n.translate(-h,s),n.scale(1,-1);break;case 8:l.width=s,l.height=h,n.translate(0,h),n.rotate(1.5*Math.PI)}return n.drawImage(t,a,r,h,s),n.restore(),l};
                    var orientation = parseInt( data.exif.get( 'Orientation' ) );
                    imgData = drawImage( img , orientation , 0 , 0 , img.width , img.height ).toDataURL("image/jpeg" ,0.89 );;
                }
                whenImageLoaded( imgData );
            } , {
                maxWidth: that._options.maxwidth ? that._options.maxwidth : 2000,
                canvas: true,
                pixelRatio: window.devicePixelRatio,
                downsamplingRatio: 0.5,
                orientation: true
            } );

        } else {

            var xhr = this._xhrs[id] = new XMLHttpRequest();
            var self = this;

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    self._loaded[id] = e.loaded;
                    self._options.onProgress(id, name, e.loaded, e.total);
                }
            };

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    self._onComplete(id, xhr);
                }
            };

            // build query string
            params = params || {};
            params['qqfile'] = name;
            var queryString = qq.obj2url(params, this._options.action);

            xhr.open("POST", queryString, true);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhr.setRequestHeader("X-File-Name", encodeURIComponent(name));
            xhr.setRequestHeader("Content-Type", "application/octet-stream");
            xhr.send(file);
        }

    },
    _onComplete : function(id, xhr) {
        // the request was aborted/cancelled
        if (!this._files[id])
            return;

        var name = this.getName(id);
        var size = this.getSize(id);

        this._options.onProgress(id, name, size, size);

        if (xhr.status == 200) {
            this.log("xhr - server response received");
            this.log("responseText = " + xhr.responseText);

            var response;

            try {
                response = eval("(" + xhr.responseText + ")");
            } catch (err) {
                response = {};
            }

            this._options.onComplete(id, name, response);

        } else {
            this._options.onComplete(id, name, {});
        }

        this._files[id] = null;
        this._xhrs[id] = null;
        this._dequeue(id);
    },
    _cancel : function(id) {
        this._options.onCancel(id, this.getName(id));

        this._files[id] = null;

        if (this._xhrs[id]) {
            this._xhrs[id].abort();
            this._xhrs[id] = null;
        }
    }
});
