var NewsEasy = new Class({

    Implements: [ Options ],

    options: {
        mode: 'inject',
        delay: 500
    },

    container: null,
    newsSection: null,
    newsHandle: null,
    intTimeoutId: 0,
    shouldRun: true,
    isCollapsed: null,
    newsSectionLoaded: false,

    initialize: function (options) {
        var self = this;
        this.setOptions(options);

        this.newsSection = document.getElementById('tl_navigation').getElements('.newseasy_toggle')[0];
        this.container = document.getElementById('newseasy');

        // get state
        this.isCollapsed = (this.newsSection.hasClass('newseasy_collapsed')) ? true : false;

        // check if the news section content is loaded or if it is going to be loaded via ajax on the next click
        this.newsSectionLoaded = Boolean($$('#tl_navigation a.news').length);

        // initialize newseasy again when someone toggles the section
        this.newsSection.addEvent('click', function () {
            // update state
            alert('huhu');
            self.isCollapsed = !self.isCollapsed;
            self.init();
        });

        window.addEvent('ajax_change', function () {
            self.newsSectionLoaded = true;
            self.init();
        });

        this.init();
    },

    init: function () {
        var self = this;

        // only launch newseasy if expanded and the data doesn't need to be loaded via ajax first
        if (!this.isCollapsed && this.newsSectionLoaded) {
            this.newsHandle = $$('#tl_navigation a.news')[0].getParent('li');
            this.container.inject(this.newsHandle);
            this.container.removeClass('newseasy_doNotLaunch');
        }
        else {
            this.container.addClass('newseasy_doNotLaunch');
            return;
        }

        // Set item to display: block when everything is done
        this.container.addClass('ready');
    }
});