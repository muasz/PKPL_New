# PierceFlow Railway Deployment

## Railway Deployment Steps

### 1. Prepare Railway Files
- ✅ railway.json (Railway configuration)
- ✅ Procfile (Process definition)
- ✅ composer.json (PHP dependencies)
- ✅ nginx.conf (Web server config)
- ✅ Database migration script

### 2. Environment Variables Setup
```
DATABASE_URL=mysql://username:password@host:port/database
WHATSAPP_API_TOKEN=your_fonnte_token
ADMIN_PHONE=6281234567890
PRODUCTION_MODE=true
```

### 3. Deploy Process
1. Connect GitHub repository to Railway
2. Set environment variables
3. Deploy with automatic builds
4. Setup custom domain (optional)

### 4. Database Setup
Railway will provide MySQL database automatically.
Migration will be handled via deployment script.

## Benefits of Railway:
- 🚀 Auto-deployment from GitHub
- 💰 $5/month starter (very affordable)
- 🔒 Free SSL certificates
- 📊 Built-in monitoring
- 🌍 Global CDN
- 💾 Managed MySQL database
- 🔧 Easy environment variables
- 📱 Mobile app for monitoring

## Cost Estimation:
- **Railway Starter**: $5/month
- **Custom domain**: $10-15/year (optional) 
- **WhatsApp API**: ~Rp 75/message (Fonnte)
- **Total**: ~$5-7/month for full production setup

## Step-by-Step Deployment Guide

### 1. **Create Railway Account**
   - Visit https://railway.app
   - Sign up with GitHub account (recommended)
   - Verify your account and add payment method ($5/month)

### 2. **Deploy from GitHub**
   ```bash
   # Push your code to GitHub first
   git add .
   git commit -m "Prepare for Railway deployment"
   git push origin main
   ```

### 3. **Create Railway Project**
   - Click "New Project" in Railway dashboard
   - Select "Deploy from GitHub repo"  
   - Choose your repository
   - Railway will auto-detect PHP and deploy using our `railway.json` config

### 4. **Add MySQL Database**
   - In your Railway project dashboard
   - Click "New" → "Database" → "Add MySQL"
   - Railway will automatically provide `DATABASE_URL` environment variable

### 5. **Configure Environment Variables**
   - Go to your service settings → Variables tab
   - Add these environment variables:
     ```
     WHATSAPP_PROVIDER=fonnte
     FONNTE_TOKEN=your_actual_fonnte_token
     PRODUCTION_MODE=true
     WHATSAPP_PRODUCTION=true
     APP_NAME=PierceFlow Studio
     DEBUG_MODE=false
     ```

### 6. **Setup Database Tables**
   - Visit: `https://your-app.up.railway.app/health.php` (check system status)
   - Visit: `https://your-app.up.railway.app/includes/db.php?setup=db` (setup tables)
   - This will create all necessary tables and default admin account

### 7. **Verify Deployment**
   - Visit your app URL: `https://your-app.up.railway.app`
   - Login as admin: `admin@pierceflow.com` / `admin123`
   - Test WhatsApp notifications in admin panel
   - **Change admin password immediately!**

### 8. **Production Checklist**
   - [ ] Database connected and tables created
   - [ ] WhatsApp API configured and tested
   - [ ] Admin password changed from default
   - [ ] Test booking flow end-to-end
   - [ ] Verify WhatsApp notifications work
   - [ ] Check health endpoint: `/health.php`