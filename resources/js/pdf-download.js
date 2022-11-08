PdfDownload = {
    waitingScreenHtml: function (translation) {
        return "<html><head><style>" +
            "#animation {" +
            "background-image: url(/img/loading.gif);" +
            "}" +
            "</style></head>" +
            "<body style='background: #f5f5f5'>" +
            "<div style='display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh;'>" +
            "<div style='background-color: rgba(0,0,0,0.8); padding: 20px 20px 15px 20px; border-radius: 10px; margin-bottom: 1rem;'>" +
            "<div id='animation' style='width: 35px; height: 35px;'></div>" +
            "</div>" +
            "<span style='font-family: Nunito, sans-serif; font-size: 20pt;'>" +
            translation +
            "</span>" +
            "</div>" +
            "</body></html>";
    },

}