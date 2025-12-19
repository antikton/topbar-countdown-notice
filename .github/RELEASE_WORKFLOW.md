# Workflow para crear releases en GitHub

## Flujo de trabajo recomendado

### 1. Hacer cambios y commit
```bash
git add .
git commit -m "Tu mensaje de commit"
git push origin master
```

### 2. Crear tag anotado con el changelog
```bash
# Copia el changelog de readme.txt para la versión
# Por ejemplo, para v1.1.1:
git tag -a v1.1.1 -m "## Changelog

- Updated: Modern, eye-catching plugin banners with updated \"Antikton Topbar Countdown\" branding
- Improved: GitHub Actions workflow now automatically extracts changelog from readme.txt
- Improved: GitHub Actions now generates plugin ZIP file and attaches it to releases
- Improved: Enhanced release notes generation with version-specific changelog integration"
```

### 3. Push del tag
```bash
git push origin v1.1.1
```

## Alternativa: Editar manualmente la release

Si prefieres no usar tags anotados:

1. Deja que el workflow cree la release automáticamente (como ahora)
2. Ve a GitHub → Releases → Edita la release v1.1.1
3. Copia y pega el changelog del `readme.txt` manualmente
4. Guarda los cambios

## Ventajas del tag anotado

✅ El changelog está en el tag mismo  
✅ GitHub Actions usa el mensaje del tag automáticamente  
✅ No hay problemas de interpretación de shell  
✅ Más control sobre el contenido de las release notes  

## Desventajas

❌ Tienes que copiar manualmente el changelog al crear el tag  
❌ Requiere un paso extra en tu flujo de trabajo  
