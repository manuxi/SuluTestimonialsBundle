import React from 'react';
import starRatingListStyles from './starRatingList.scss';
console.log('StarRatingList transformer module loaded');
class StarRatingList {
    transform(value, parameters, context) {
        const rating = value ? parseInt(String(value), 10) : 0;
        const styles = starRatingListStyles || {};
        console.log('StarRatingList transform:', { value, rating, styles });
        // Detect scale: if rating > 5, assume 10-point scale
        const maxValue = rating > 5 ? 10 : 5;
        const displayStars = 5; // Always show 5 star symbols
        const stars = [];
        if (maxValue === 10) {
            // 10-point scale: use half-star increments
            for (let i = 1; i <= displayStars; i++) {
                const starValue = i * 2; // 2, 4, 6, 8, 10
                const halfStarValue = starValue - 1; // 1, 3, 5, 7, 9
                let starClass = styles.empty;
                let starChar = '☆';
                if (rating >= starValue) {
                    // Full star
                    starClass = styles.filled;
                    starChar = '★';
                } else if (rating >= halfStarValue) {
                    // Half star
                    starClass = styles.half;
                    starChar = '⯪';
                }
                stars.push(
                    <span key={i} className={starClass}>
                        {starChar}
                    </span>
                );
            }
        } else {
            // 5-point scale: simple full stars
            for (let i = 1; i <= displayStars; i++) {
                const isFilled = i <= rating;
                stars.push(
                    <span
                        key={i}
                        className={isFilled ? styles.filled : styles.empty}
                    >
                        {isFilled ? '★' : '☆'}
                    </span>
                );
            }
        }
        return (
            <span className={styles.container}>
                {stars}
                <span className={styles.value}>({rating}/{maxValue})</span>
            </span>
        );
    }
}
export default StarRatingList;