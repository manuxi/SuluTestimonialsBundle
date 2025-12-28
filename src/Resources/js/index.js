// @flow
import {fieldRegistry, listFieldTransformerRegistry} from 'sulu-admin-bundle/containers';
//import StarRating from './fieldTypes/StarRating';
import StarRatingList from './listFieldTransformers/StarRatingList';

//fieldRegistry.add('star_rating', StarRating);

listFieldTransformerRegistry.add('star_rating', new StarRatingList());