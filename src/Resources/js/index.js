// @flow
import {fieldRegistry, listFieldTransformerRegistry} from 'sulu-admin-bundle/containers';
import StarRating from './fieldTypes/StarRating';
import StarRatingInput from './fieldTypes/StarRatingInput';
import StarRatingSelect from './fieldTypes/StarRatingSelect';
//import StarRatingList from './listFieldTransformers/StarRatingList';

fieldRegistry.add('star_rating', StarRating);
fieldRegistry.add('star_rating_input', StarRatingInput);
fieldRegistry.add('star_rating_select', StarRatingSelect);

//listFieldTransformerRegistry.add('star_rating', new StarRatingList());