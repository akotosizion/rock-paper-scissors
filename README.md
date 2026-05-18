# Rock Paper Scissors Game

A fun interactive Rock Paper Scissors game built with PHP and JavaScript.

## Features

- Player name registration
- Real-time game play
- Score tracking (saved to JSON)
- Responsive design
- Animated game results

## Project Structure

```
.
├── rock_paper_scissor.php    # Main game logic
├── scores.json                # Player scores storage
├── background_img/            # Background images
├── gang_sign/                 # Hand gesture images
├── sample_result/             # Sample game result images
└── README.md                  # This file
```

## Local Setup

1. Place project in your web root (e.g., `htdocs` for XAMPP)
2. Start your PHP server
3. Navigate to the application in your browser

## Deployment to Vercel

This project is configured for deployment on Vercel using the Vercel PHP runtime.

### Steps to Deploy:

1. **Push to GitHub:**
   ```bash
   git add .
   git commit -m "Initial commit: Rock Paper Scissors game"
   git branch -M main
   git remote add origin https://github.com/YOUR_USERNAME/rock-paper-scissors.git
   git push -u origin main
   ```

2. **Deploy to Vercel:**
   - Go to [vercel.com](https://vercel.com)
   - Click "New Project"
   - Import the GitHub repository
   - Vercel will automatically detect the PHP configuration
   - Click "Deploy"

## Technologies Used

- PHP 7.4+
- JavaScript (Vanilla)
- JSON (Score storage)
- CSS3 (Styling)
- HTML5

## License

MIT
