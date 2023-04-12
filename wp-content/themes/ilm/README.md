# ILM WordPress

## Deploying to production

In the theme directory:

```
zip -FSr ilm.zip . -x "node_modules/*" ".git/*"
```

Upload `ilm.zip` via WP Admin > Themes and replace current.