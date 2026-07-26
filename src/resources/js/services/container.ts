import { Container } from 'inversify';
import { TYPES } from './types';
import { LightboxService } from './LightboxService';

const container = new Container();

container.bind(TYPES.LightboxService).to(LightboxService).inSingletonScope();

export { container, TYPES };
