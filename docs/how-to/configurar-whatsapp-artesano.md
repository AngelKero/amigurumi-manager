# Guía How-To: Configuración y Validación de WhatsApp Artesanal (E.164)

Esta guía explica cómo configurar, formatear y validar el número de contacto de WhatsApp para cada artesana o artesano en **Crochet Manager**, asegurando la interoperabilidad con enlaces directos universales y cumpliendo el estándar internacional **E.164**.

---

## 1. Importancia del Estándar E.164

La plataforma prescinde de intermediarios en la negociación de encargos personalizados, facilitando la comunicación directa a través de enlaces con el esquema `https://wa.me/{numero}`. 

Para que estos enlaces funcionen en cualquier dispositivo móvil o de escritorio a nivel global:
- El número telefónico debe estar normalizado según el estándar internacional **E.164**.
- No debe contener espacios en blanco, guiones, signos de suma (`+`) ni paréntesis en la cadena enviada a la API.
- Debe incluir obligatoriamente el código de marcación internacional del país seguido de los dígitos del abonado móvil.

---

## 2. Reglas de Normalización y Validación

En México, el código internacional del país es **+52**. El plan nacional de numeración utiliza números móviles locales de **10 dígitos**.

### Esquema de Formato Válido:
- **Número local habitual:** `(55) 1234-5678` (10 dígitos).
- **En formato internacional con prefijo:** `+52 55 1234 5678`.
- **Cadena limpia almacenada en base de datos:** `525512345678` (exactamente 12 caracteres numéricos: código de país 52 + **10 dígitos** del móvil).

### Expresión Regular de Validación:
El backend y frontend aplican la siguiente regla estricta:
```regex
^[1-9]\d{9,14}$
```
Esta expresión valida que el número inicie con un dígito distinto de cero y contenga una longitud de entre 10 y 15 dígitos numéricos sin símbolos especiales.

---

## 3. Procedimiento de Configuración

### Desde el Perfil de Usuario en el Panel
1. Inicie sesión con su cuenta de artesana o artesano.
2. Acceda a la vista de perfil o configuración de cuenta.
3. Localice el campo **"WhatsApp de Contacto Directo"**.
4. Ingrese su número telefónico. Si reside en México, introduzca el prefijo **+52** o los **10 dígitos** de su línea celular. El módulo JavaScript limpiará automáticamente cualquier espacio o guión sobrante.
5. El sistema mostrará una vista previa en tiempo real del enlace de prueba:
   > *"Enlace generado: https://wa.me/525512345678"*
6. Haga clic en **"Guardar WhatsApp"** para actualizar su perfil.

### Vía API REST (`/api/usuarios/actualizar-whatsapp.php`)
Puede invocar el endpoint enviando el número normalizado:
```http
POST /api/usuarios/actualizar-whatsapp.php HTTP/1.1
Host: localhost:8000
Authorization: Bearer <TOKEN_ARTESANO>
Content-Type: application/json

{
  "whatsapp": "525512345678"
}
```

Respuesta exitosa (HTTP 200 OK):
```json
{
  "exito": true,
  "mensaje": "Número de WhatsApp comercial actualizado exitosamente.",
  "datos": {
    "usuario_id": 2,
    "whatsapp": "525512345678",
    "enlace_directo": "https://wa.me/525512345678"
  }
}
```

---

## 4. Pruebas y Diagnóstico de Enlaces

Una vez guardado el número:
1. Abra cualquier creación asociada a su autoría en el catálogo público (`/detalle.php?id=X`).
2. Compruebe que en la tarjeta de autoría del taller aparezca el botón verde con el icono oficial de WhatsApp.
3. Al pulsar el botón, WhatsApp Web o la aplicación móvil nativa debe abrirse directamente en la conversación con el mensaje de consulta prefabricado, sin arrojar el error *"El número de teléfono compartido a través de la URL es inválido"*.
4. Si se introducen menos de 10 dígitos o caracteres no numéricos, el backend rechazará la petición con `HTTP 422 Unprocessable Entity` detallando el error de validación.
