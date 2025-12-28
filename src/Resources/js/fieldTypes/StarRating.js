// @flow
import React from 'react';
import { observer } from 'mobx-react';
import { action, observable, computed } from 'mobx';
import classNames from 'classnames';
import starRatingStyles from './starRating.scss';

type Props = {
    disabled: boolean,
    onChange: (value: ?string) => void,
    value: ?string,
    schemaOptions?: {
        max_value?: {
            value: number,
        },
    },
};

@observer
class StarRating extends React.Component<Props> {
    @observable hoverValue: ?number = null;

    constructor(props: Props) {
        super(props);
    }

    @computed get maxValue(): number {
        const { schemaOptions } = this.props;
        return schemaOptions?.max_value?.value || 5;
    }

    @computed get displayStars(): number {
        // Always display 5 star symbols, regardless of scale
        return 5;
    }

    @computed get isTenScale(): boolean {
        return this.maxValue === 10;
    }

    @computed get currentValue(): number {
        const { value } = this.props;
        return value ? parseInt(value, 10) : 0;
    }

    @action handleMouseEnter = (starIndex: number, isHalf: boolean) => {
        if (!this.props.disabled) {
            if (this.isTenScale) {
                // 10-point scale: half stars
                this.hoverValue = isHalf ? (starIndex * 2 - 1) : (starIndex * 2);
            } else {
                // 5-point scale: full stars only
                this.hoverValue = starIndex;
            }
        }
    };

    @action handleMouseLeave = () => {
        this.hoverValue = null;
    };

    handleClick = (starIndex: number, isHalf: boolean) => {
        if (!this.props.disabled) {
            let newValue: number;
            if (this.isTenScale) {
                newValue = isHalf ? (starIndex * 2 - 1) : (starIndex * 2);
            } else {
                newValue = starIndex;
            }
            this.props.onChange(String(newValue));
        }
    };

    getStarState(starIndex: number): { isFilled: boolean, isHalf: boolean, isHovered: boolean } {
        const displayValue = this.hoverValue !== null ? this.hoverValue : this.currentValue;

        if (this.isTenScale) {
            const fullValue = starIndex * 2;
            const halfValue = starIndex * 2 - 1;

            return {
                isFilled: displayValue >= fullValue,
                isHalf: displayValue === halfValue,
                isHovered: this.hoverValue !== null && displayValue >= halfValue,
            };
        } else {
            return {
                isFilled: starIndex <= displayValue,
                isHalf: false,
                isHovered: this.hoverValue !== null && starIndex <= this.hoverValue,
            };
        }
    }

    renderStar = (starIndex: number) => {
        const { isFilled, isHalf, isHovered } = this.getStarState(starIndex);

        const starClass = classNames(
            starRatingStyles.star,
            {
                [starRatingStyles.filled]: isFilled,
                [starRatingStyles.half]: isHalf,
                [starRatingStyles.hovered]: isHovered,
                [starRatingStyles.disabled]: this.props.disabled,
            }
        );

        let starChar = '☆';
        if (isFilled) {
            starChar = '★';
        } else if (isHalf) {
            starChar = '⯪';
        }

        if (this.isTenScale) {
            // For 10-scale, render clickable left/right halves
            return (
                <span
                    key={starIndex}
                    className={starClass}
                    role="button"
                    tabIndex={this.props.disabled ? -1 : 0}
                    aria-label={`Rate ${starIndex} of ${this.displayStars}`}
                >
                    <span
                        className={starRatingStyles.halfLeft}
                        onMouseEnter={() => this.handleMouseEnter(starIndex, true)}
                        onMouseLeave={this.handleMouseLeave}
                        onClick={() => this.handleClick(starIndex, true)}
                    />
                    <span
                        className={starRatingStyles.halfRight}
                        onMouseEnter={() => this.handleMouseEnter(starIndex, false)}
                        onMouseLeave={this.handleMouseLeave}
                        onClick={() => this.handleClick(starIndex, false)}
                    />
                    {starChar}
                </span>
            );
        }

        return (
            <span
                key={starIndex}
                className={starClass}
                onMouseEnter={() => this.handleMouseEnter(starIndex, false)}
                onMouseLeave={this.handleMouseLeave}
                onClick={() => this.handleClick(starIndex, false)}
                role="button"
                tabIndex={this.props.disabled ? -1 : 0}
                aria-label={`Rate ${starIndex} of ${this.displayStars}`}
            >
                {starChar}
            </span>
        );
    };

    render() {
        const stars = [];
        for (let i = 1; i <= this.displayStars; i++) {
            stars.push(this.renderStar(i));
        }

        return (
            <div className={starRatingStyles.container}>
                <div className={starRatingStyles.stars}>
                    {stars}
                </div>
                <span className={starRatingStyles.value}>
                    {this.currentValue}/{this.maxValue}
                </span>
            </div>
        );
    }
}

export default StarRating;