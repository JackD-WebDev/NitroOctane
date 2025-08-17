import { defineFormKitConfig } from '@formkit/vue';
import { en, es, fr } from '@formkit/i18n';

const tl = {
  ui: {
    add: 'Magdagdag',
    remove: 'Tanggalin',
    submit: 'Isumite',
    noFiles: 'Walang napiling file',
  },
  validation: {
    accepted: 'Pakitanggap ang {label}.',
    date_after: 'Ang {label} ay dapat pagkatapos ng {date}.',
    alpha: 'Ang {label} ay dapat lamang maglaman ng mga letra.',
    alphanumeric:
      'Ang {label} ay dapat lamang maglaman ng mga letra at numero.',
    between: 'Ang {label} ay dapat nasa pagitan ng {min} at {max}.',
    confirm: 'Ang {label} ay hindi tumutugma.',
    email: 'Ang {label} ay hindi isang wastong email address.',
    ends_with: 'Ang {label} ay dapat magtapos sa {value}.',
    length: 'Ang {label} ay dapat {length} karakter.',
    matches: 'Ang {label} ay hindi tumutugma sa kinakailangan.',
    max: 'Ang {label} ay hindi dapat higit sa {max}.',
    min: 'Ang {label} ay dapat hindi bababa sa {min}.',
    not: 'Ang {label} ay may hindi pinapahintulutang halaga.',
    number: 'Ang {label} ay dapat isang numero.',
    required: 'Kinakailangan ang {label}.',
    starts_with: 'Ang {label} ay dapat magsimula sa {value}.',
    url: 'Ang {label} ay hindi isang wastong URL.',
  },
};

export default defineFormKitConfig({
  // rules: {},
  locales: {
    en: en,
    es: es,
    fr: fr,
    tl: tl,
  },
});
