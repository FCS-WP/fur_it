export const moduleColumns = ["ID", "NAME", "ACTIVE"];
export const moduleColumnWidths = {
  module_name: "30%",
  active: "auto",
};
export const settingsHeadcells = [
  {
    id: "name",
    numeric: false,
    disablePadding: true,
    label: "Name",
  },
  {
    id: "active",
    numeric: false,
    disablePadding: true,
    label: "Active",
  },
];

export const convertSlugToName = (slug) => {
  if (!slug || typeof slug !== "string") return "";
  const name = slug.replace(/[-_]/g, " ");
  return name.replace(/\b\w/g, (char) => char.toUpperCase());
};

export const convertNameToSlug = (name) => {
  if (typeof name !== 'string') return '';

  return name
    .trim()                              
    .toLowerCase()                    
    .normalize('NFD')            
    .replace(/[\u0300-\u036f]/g, '')   
    .replace(/[^a-z0-9]+/g, '-')    
    .replace(/^-+|-+$/g, '')
    .replace(/-{2,}/g, '-');
};

export const orderSettingCells = [
  {
    id: "name",
    numeric: false,
    disablePadding: true,
    label: "Name",
  },
  {
    id: "active",
    numeric: false,
    disablePadding: true,
    label: "Active",
  },
]