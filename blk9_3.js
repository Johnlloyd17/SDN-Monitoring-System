(function() {
        document.body.classList.add('fixed');

        var currentPath = window.location.pathname;
        var links = document.querySelectorAll('.sidebar-menu a');
        for (var i = 0; i < links.length; i++) {
            var href = links[i].getAttribute('href');
            if (!href || href === '#') continue;
            var linkPath;
            try {
                linkPath = new URL(href, window.location.href).pathname;
            } catch (e) {
                continue;
            }
            if (linkPath === currentPath) {
                var li = links[i].closest('li');
                if (li) li.classList.add('active');
                var parentTreeview = links[i].closest('.treeview');
                if (parentTreeview) {
                    parentTreeview.classList.add('active', 'menu-open');
                    var submenu = parentTreeview.querySelector('.treeview-menu');
                    if (submenu) submenu.style.display = 'block';
                }
                break;
            }
        }
    })();