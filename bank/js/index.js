/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Other/javascript.js to edit this template
 @Creado: 30/11/2024 4:21:48 p. m.
 @Autora: Daniela Sierra Vergel 
 */

setTimeout(function () {
    // Obtener los elementos de los selects
    const incluirBankSelect = document.getElementById('id_incluirBank');
    const privacyBankDiv = document.getElementById('fitem_id_privacyBank');
    const check = document.getElementById('id_coursecontentnotification').parentElement.parentElement;
    
    // Función para manejar el cambio de estado en "Incluir en el Banco de actividades"
    const handleIncluirBankChange = () => {
        if (incluirBankSelect.value === "1") privacyBankDiv.style.display = "flex"; // Mostrar el select de privacidad
        else  privacyBankDiv.style.display = "none";// Ocultar el select de privacidad

    };
    
    // Agregar el evento de cambio al select "Incluir en el Banco de actividades"
    incluirBankSelect.addEventListener('change', handleIncluirBankChange);

    // Llamar a la función una vez para configurar el estado inicial
    handleIncluirBankChange();
    
    
    // Obtener el grupo de botones por su ID
    const btnsSave = document.getElementById('fgroup_id_buttonar');

    // Verificar si el grupo de botones existe
    if (btnsSave) {
        const form = btnsSave.closest('form');// Obtener el contenedor padre del formulario
        btnsSave.parentNode.removeChild(btnsSave);// Eliminar el grupo de botones de su contenedor actual
        check.parentNode.removeChild(check);// Eliminar el grupo de botones de su contenedor actual
        form.appendChild(check);// Insertar el grupo de botones al final del formulario
        form.appendChild(btnsSave);// Insertar el grupo de botones al final del formulario
    }
    
}, 1500);

