import { startStimulusApp } from '@symfony/stimulus-bridge';
import media_controller from "./controllers/media_controller";
import cropper_controller from './controllers/cropper_controller';

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(require.context(
  '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
  true,
  /\.(j|t)sx?$/
));

// register any custom, 3rd party controllers here
app.register('media_controller', media_controller);
app.register('cropper_controller', cropper_controller);
