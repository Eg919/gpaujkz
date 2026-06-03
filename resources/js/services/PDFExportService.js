import html2pdf from 'html2pdf.js';

/**
 * Service centralisé pour l'exportation de documents en PDF.
 * Configure automatiquement le format A3 Paysage pour les rapports denses.
 */
export const PDFExportService = {
    /**
     * Exporte un élément HTML vers un fichier PDF.
     * @param {HTMLElement} element - L'élément DOM à capturer (ex: document.querySelector('.mt-4'))
     * @param {string} filename - Le nom du fichier généré (ex: 'Rapport_Global.pdf')
     */
    async export(element, filename = 'export.pdf') {
        if (!element) {
            console.error("PDFExportService: L'élément à exporter est introuvable.");
            return;
        }

        // Activer le mode PDF (styles de contraste, etc.)
        element.classList.add('pdf-export-active');

        const options = {
            margin: [15, 10, 15, 10], 
            filename: filename,
            image: { type: 'jpeg', quality: 0.98 }, 
            html2canvas: { 
                scale: 2, // Plus stable pour les très grands rapports A3
                useCORS: true,
                letterRendering: true,
                scrollY: 0,
                backgroundColor: '#ffffff',
                windowWidth: element.scrollWidth,
                windowHeight: element.scrollHeight,
                logging: false
            },
            jsPDF: { 
                unit: 'mm', 
                format: 'a3', 
                orientation: 'landscape', 
                compress: true,
                precision: 16
            },
            pagebreak: { 
                mode: ['css', 'legacy'], // 'avoid-all' supprimé car il forçait tout le tableau à sauter
                avoid: ['tr', '.signature-block-container', '.signature-box-protected-roc']
            }
        };

        try {
            await html2pdf().from(element).set(options).save();
        } finally {
            // Désactiver le mode PDF après capture
            element.classList.remove('pdf-export-active');
        }
    }
};

export default PDFExportService;
