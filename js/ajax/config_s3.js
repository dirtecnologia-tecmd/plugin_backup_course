class Configurar_S3 {

    constructor() {

        // Llama al método que inicializa los botones cuando se crea una instancia de la clase
        this.initButtons();
        //Consultar
        this.consultarRegistros();
    }

    initButtons() {
        // Selecciona los botones por su clase, id o cualquier otro selector
        const guardar = document.querySelector('#create_s3');
        guardar.addEventListener('click', this.guardar_s3);
    }

    consultarRegistros() {

        const formData = new FormData();

        formData.append('function', 1);

        fetch('../../methods/configurar_s3.php', {
            method: 'POST',
            body: formData
        }).then(response => {
            if (!response.ok) {
                throw new Error('Error en la solicitud');
            }
            return response.text();
        }).then(data => {

            const datos = JSON.parse(data);

            if (datos.state) {

                const bucket = document.getElementById('id_bucket');

                bucket.value = datos.bucket;

                const public_key = document.getElementById('id_public_key');

                public_key.value = datos.public_key;

                const private_key = document.getElementById('id_private_key');

                private_key.value = datos.private_key;

                const guardar = document.querySelector('#create_s3');

                guardar.innerHTML = 'Actualizar';
            }

        }).catch(error => {
            alert('Error en la solicitud: ' + error);
        });
    }

    guardar_s3() {

        const bucket = document.getElementById('id_bucket');

        const public_key = document.getElementById('id_public_key');

        const private_key = document.getElementById('id_private_key');

        if (bucket.value.length === 0) {
            msjBC.error('ERROR', 'El Bucket es obligatorio');
        } else if (public_key.value.length === 0) {
            msjBC.error('ERROR', 'La llave pública es obligatoria');
        } else if (private_key.value.length === 0) {
            msjBC.error('ERROR', 'La llave privada es obligatoria');
        } else {

            const formData = new FormData();

            formData.append('function', 0);
            formData.append('bucket', bucket.value);
            formData.append('public_key', public_key.value);
            formData.append('private_key', private_key.value);

            fetch('../../methods/configurar_s3.php', {
                method: 'POST',
                body: formData
            }).then(response => {
                if (!response.ok) {
                    throw new Error('Error en la solicitud');
                }
                return response.text();
            })
                .then(data => {

                    const datos = JSON.parse(data);

                    if (datos.state === 'ERROR') {
                        msjBC.error(datos.state, datos.msj);
                    } else if (datos.state === 'OK') {
                        msjBC.ok(datos.state, datos.msj);
                    }

                })
                .catch(error => {
                    alert('Error en la solicitud: ' + error);
                });

        }

    }

}

const configurar_s3 = new Configurar_S3();