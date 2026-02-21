<!DOCTYPE html>
<html>
<head>
    <title>New Brand Created</title>
</head>
<body>
    <h1>New Brand Created</h1>
    <p>Brand Name: {{ $brand->name }}</p>
    <p>Slug: {{ $brand->slug }}</p>
    <p>Status: {{ $brand->status ? 'Active' : 'Inactive' }}</p>
</body>
</html>
