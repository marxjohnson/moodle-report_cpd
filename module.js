M.report_cpd = {

    printurl: '',

    init: function(Y, print, printurl) {
        if (print) {
            window.print();
            window.close();
        } else {
            this.printurl = printurl;
            buttons = Y.all('.cpd_print_button form div input[type=submit]');
            buttons.setAttribute('type', 'button');
            buttons.on('click', this.print);
        }
    },

    print: function() {
        alert(M.util.get_string('printlandscape', 'report_cpd'));
        window.open(M.report_cpd.printurl, "", "resizable=yes toolbar=no, location=no");
    }

}
