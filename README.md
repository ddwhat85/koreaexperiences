# koreaexperiences.com

Static travel guide site + TourAPI proxy for Korea experiences.

## Files

- `index.html` — Main site (static, no build step)
- `server.js` — TourAPI CORS proxy (Node.js/Express)
- `package.json` — Node dependencies

## Deploy (Cloudways)

1. Push this repo to GitHub
2. In Cloudways: Create App → Node.js → Connect GitHub repo
3. Set environment variable: `TOUR_API_KEY=your_key_here`
4. Start command: `npm start`
5. Point `www.koreaexperiences.com` DNS A record to Cloudways server IP

## TourAPI Key

Register at https://www.data.go.kr → 한국관광공사_영문관광정보서비스_GW → get Decoding key.
