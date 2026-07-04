<!DOCTYPE html>
<html>
<head>
    @viteReactRefresh
    @vite([
        'resources/js/app.tsx',
        'resources/assets/sass/app.scss'
    ], 'build-website')

    @inertiaHead
</head>

<body>

    @inertia

</body>
</html>