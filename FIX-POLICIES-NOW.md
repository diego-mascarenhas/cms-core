# IMPORTANTE: Aplicar Fix de Políticas en Instalación Existente

## El Problema

El usuario "member" puede ver la lista de usuarios cuando NO debería poder hacerlo.

## La Causa

Las políticas (`PostPolicy` y `UserPolicy`) no están siendo aplicadas porque:
1. El `AuthServiceProvider` no está publicado en la aplicación
2. O no está registrado en `bootstrap/providers.php`

## La Solución

En tu aplicación **Box Shows** (admin.boxshows.test), ejecuta:

```bash
cd /path/to/boxshows

# 1. Actualizar el paquete (si no lo has hecho)
composer update idoneo/cms-core

# 2. Publicar políticas y provider
php artisan cms-core:update --force

# 3. Verificar que todo esté correcto
php artisan cms-core:diagnose-policies

# 4. Limpiar cachés
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# 5. Verificar bootstrap/providers.php
cat bootstrap/providers.php | grep AuthServiceProvider
```

## Verificación Manual

Si el comando no registró automáticamente el `AuthServiceProvider`, agrégalo manualmente:

**Archivo: `bootstrap/providers.php`**

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,  // ← Agregar esta línea
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\JetstreamServiceProvider::class,
];
```

## Verificar Políticas

Después de aplicar los cambios:

1. Cierra sesión como "pepe" (member)
2. Inicia sesión como "Admin" (hola@humano.app)
   - Deberías ver "Usuarios" en el menú
3. Inicia sesión como "pepe" (member)
   - **NO** deberías ver "Usuarios" en el menú
   - Si vas a `/admin/users` directamente, deberías ver error 403

## Diagnóstico

Ejecuta este comando para ver el estado de las políticas:

```bash
php artisan cms-core:diagnose-policies
```

Debe mostrar:
- ✓ AuthServiceProvider is registered
- ✓ PostPolicy.php exists
- ✓ UserPolicy.php exists
- ✓ UserPolicy registered
- ✓ PostPolicy registered

## Si Sigue Sin Funcionar

1. Verifica que existan los archivos:
   ```bash
   ls -la app/Policies/
   ls -la app/Providers/AuthServiceProvider.php
   ```

2. Verifica el contenido de `bootstrap/providers.php`:
   ```bash
   cat bootstrap/providers.php
   ```

3. Limpia TODOS los cachés:
   ```bash
   php artisan optimize:clear
   ```

4. Reinicia el servidor (si usas Herd, reinicia Herd)

## Resultado Esperado

**Como Admin:**
- ✅ Ve menú "Usuarios"
- ✅ Puede crear/editar/eliminar usuarios
- ✅ Ve todos los posts
- ✅ Puede crear categorías

**Como Member:**
- ❌ NO ve menú "Usuarios"
- ❌ Si intenta acceder a `/admin/users` → Error 403
- ✅ Solo ve sus propios posts
- ✅ Puede crear tags pero NO categorías
