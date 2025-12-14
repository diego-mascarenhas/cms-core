# Pasos para Actualizar Box Shows a v1.4.2

## ✅ Versión v1.4.2 Creada (FIX CRÍTICO)

- **Commit**: Pending
- **Tag**: v1.4.2
- **Fix**: AdminPanelProvider ahora se publica correctamente

---

## 📤 1. Push al Repositorio (Hazlo Ahora)

```bash
cd /Users/magoo/Sites/cms-core

# Push commits
git push origin main

# Push tags (v1.4.2 se creará después del commit)
git push origin --tags
```

---

## 🔄 2. Actualizar Box Shows (IMPORTANTE: Usar --force)

```bash
cd /Users/magoo/Sites/admin.boxshows

# A. Actualizar el paquete
composer update idoneo/cms-core

# B. Publicar archivos (CRÍTICO: usar --force para sobrescribir AdminPanelProvider)
php artisan cms-core:update --force

# C. Verificar que las políticas están correctas
php artisan cms-core:diagnose-policies

# D. Limpiar todos los cachés
php artisan optimize:clear
```

---

## ✨ 3. Qué Se Va a Corregir

### Fix #1: Logout Funcional
- ✅ El botón "Salir" funcionará sin error 405
- ✅ Usa método POST correctamente

### Fix #2: Rol Preseleccionado
- ✅ Al crear usuario, "Member" aparecerá seleccionado
- ✅ No más campo vacío confuso

### Fix #3: Políticas Activas
- ✅ Usuario "pepe" (member) NO verá el menú "Usuarios"
- ✅ Si intenta acceder a `/admin/users` → Error 403
- ✅ Solo verá sus propios posts

### Fix #4: Comando de Diagnóstico
- ✅ Nuevo comando: `php artisan cms-core:diagnose-policies`
- ✅ Verifica que todo esté configurado correctamente

---

## 🧪 4. Verificación (Después de Actualizar)

### Como Admin (hola@humano.app):
1. Login como admin
2. ✅ Deberías ver "Usuarios" en el menú
3. ✅ Click en "Crear Usuario"
4. ✅ El campo "Rol" debe mostrar "Member" seleccionado
5. ✅ Click en "Salir" debe funcionar sin error

### Como Member (pepe@pepe.com):
1. Login como pepe
2. ❌ NO debes ver "Usuarios" en el menú
3. ❌ Si vas a `/admin/users` → Debe dar error 403
4. ✅ Solo debes ver tus propios posts en "Posts"
5. ✅ Click en "Salir" debe funcionar sin error

---

## 🚨 Si Algo No Funciona

### Las políticas no funcionan (member sigue viendo usuarios):

```bash
# 1. Verificar diagnóstico
php artisan cms-core:diagnose-policies

# 2. Verificar que AuthServiceProvider está registrado
cat bootstrap/providers.php | grep AuthServiceProvider

# 3. Si no está, agrégalo manualmente a bootstrap/providers.php:
# App\Providers\AuthServiceProvider::class,

# 4. Limpiar todo
php artisan optimize:clear

# 5. Cerrar sesión y volver a entrar
```

### El rol sigue sin aparecer preseleccionado:

```bash
# 1. Verificar que no hay UserResource local
ls -la app/Filament/Resources/UserResource.php

# 2. Si existe, eliminarlo (el del paquete es mejor)
rm app/Filament/Resources/UserResource.php

# 3. Limpiar cachés
php artisan view:clear && php artisan cache:clear
```

---

## 📋 Resumen de Cambios en v1.4.1

### Nuevo:
- Comando `cms-core:diagnose-policies`
- Guía FIX-POLICIES-NOW.md
- Documentación RELEASE-v1.4.0.md

### Corregido:
- Error 405 en logout
- Rol no preseleccionado
- Mejor manejo de valores por defecto

### Mejorado:
- Diagnóstico de políticas
- Documentación de troubleshooting

---

## ✅ Checklist Final

- [ ] Hice push de v1.4.0 y v1.4.1
- [ ] Actualicé Box Shows con `composer update`
- [ ] Ejecuté `php artisan cms-core:update --force`
- [ ] Ejecuté `php artisan cms-core:diagnose-policies` (todo en verde)
- [ ] Limpié cachés
- [ ] Probé logout como admin ✓
- [ ] Probé logout como member ✓
- [ ] Member NO ve "Usuarios" ✓
- [ ] Rol "Member" aparece preseleccionado ✓

---

🎉 **¡Listo! Box Shows actualizado a v1.4.1**
