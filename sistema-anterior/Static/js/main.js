
$(document).ready(function() {
    
    // PAGE OVERLAY
    $('#content-container-left').niftyOverlay();

    zk.startRouter();
});


const { jsPDF } = window.jspdf;
function convertirCanvasAPdf(canvas) {
    
    // 1. Obtener la Data URL de la imagen (preferiblemente JPEG para archivos más pequeños)
    // El formato PNG (por defecto) es mejor para calidad, pero genera archivos más grandes.
    const imagenDataURL = canvas.toDataURL('image/jpeg', 3.0); // Calidad al 100%

    // 2. Crear una nueva instancia de jsPDF
    const doc = new jsPDF({
        orientation: "p", // Orientación: 'p' (Portrait) o 'l' (Landscape)
        unit: "mm",       // Unidades de medida: 'mm', 'cm', 'in', 'pt'
        format: "a4"      // Formato de página: 'a4', 'letter', etc.
    });

    // Calcular las dimensiones del canvas y la página para escalado
    const imgWidth = 210; // Ancho A4 en mm (210mm)
    const pageHeight = 295; // Alto A4 en mm (297mm - un pequeño margen)
    const imgHeight = (canvas.height * imgWidth) / canvas.width;
    let heightLeft = imgHeight;
    let position = 0; // Posición inicial de la imagen

    // 3. Agregar la imagen al documento
    // Si la imagen es más grande que una página, jsPDF nos permite dividirla
    doc.addImage(imagenDataURL, 'JPEG', 0, position, imgWidth, imgHeight);
    heightLeft -= pageHeight;

    // Si la imagen es más larga que una sola página A4, añade páginas adicionales
    while (heightLeft >= 0) {
        position = heightLeft - imgHeight;
        doc.addPage();
        doc.addImage(imagenDataURL, 'JPEG', 0, position, imgWidth, imgHeight);
        heightLeft -= pageHeight;
    }

    // 4. Guardar el archivo PDF
    doc.save('export.pdf');
}

function exportarCanvasPDF(elementId, callbackAfter){
    html2canvas(document.querySelector(elementId)).then(canvas => {
        convertirCanvasAPdf(canvas);
        if(callbackAfter){
            callbackAfter();
        }
    });
}