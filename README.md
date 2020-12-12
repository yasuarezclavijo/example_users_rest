## Modulo desarrollado como prueba de Ilumno (Punto 7)

Exposición de servicio REST (Resource) con dependencia sobre example_users ya que de este modulo obtendra la información

Metodos desarrollados: GET y PATCH
Forma de autenticación: Basica, para correcto funcionamiento desde POSTMAN usar Autenticacion basica con las credenciales de administrador.

1. Metodo GET: Consulta los usuarios creados a traves del formulario adicionar a la ruta /example-crud/data, un "/all" para verlos todos "/{id}" para ver uno en especifico.
2. Metodo PATH: Permite actualizar la información de un usuario en especifico a traves de indicarlo en la URL "/{id}" a traves de su ID.
  Ejemplo petición:
    ```
    {
        "id": "1",
        "name": "yasuarez",
        "identification": "22222",
        "birthdate": "1992-10-12",
        "position": "administrator",
        "state": "1"
    }
    ```
