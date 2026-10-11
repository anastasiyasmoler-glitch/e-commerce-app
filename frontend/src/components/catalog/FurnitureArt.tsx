type ArtKind = 'sofa' | 'bed' | 'table' | 'chair' | 'storage' | 'lamp' | 'linen' | 'vase' | 'hero'

const art: Record<ArtKind, string> = {
  sofa: `<ellipse cx="200" cy="322" rx="156" ry="9" fill="#2a2420" opacity=".10"/><rect x="64" y="146" width="272" height="100" rx="22" fill="#bf8a84"/><rect x="40" y="192" width="56" height="106" rx="20" fill="#d6a7a1"/><rect x="304" y="192" width="56" height="106" rx="20" fill="#d6a7a1"/><rect x="88" y="214" width="224" height="72" rx="14" fill="#d6a7a1"/><rect x="96" y="170" width="100" height="62" rx="14" fill="#e9c6c1"/><rect x="204" y="170" width="100" height="62" rx="14" fill="#e9c6c1"/>`,
  bed: `<ellipse cx="200" cy="322" rx="160" ry="9" fill="#2a2420" opacity=".10"/><rect x="70" y="104" width="260" height="124" rx="12" fill="#c3b494"/><rect x="94" y="186" width="96" height="44" rx="16" fill="#e8e0cf"/><rect x="210" y="186" width="96" height="44" rx="16" fill="#e8e0cf"/><rect x="58" y="220" width="284" height="62" rx="10" fill="#f1ece3"/><rect x="58" y="240" width="284" height="46" rx="10" fill="#d8ccb6"/><rect x="52" y="284" width="296" height="22" rx="4" fill="#b08a5c"/>`,
  table: `<ellipse cx="200" cy="322" rx="140" ry="9" fill="#2a2420" opacity=".10"/><rect x="50" y="170" width="300" height="18" rx="4" fill="#d9bb92"/><rect x="72" y="188" width="256" height="10" fill="#aa8250"/><path d="M84 198h14l-4 118H88z" fill="#c59d6e"/><path d="M302 198h14l-2 118h-6z" fill="#c59d6e"/>`,
  chair: `<ellipse cx="200" cy="322" rx="110" ry="9" fill="#2a2420" opacity=".10"/><rect x="116" y="124" width="168" height="114" rx="28" fill="#5a6774"/><rect x="88" y="178" width="50" height="112" rx="20" fill="#6f7d8b"/><rect x="262" y="178" width="50" height="112" rx="20" fill="#6f7d8b"/><rect x="126" y="210" width="148" height="72" rx="14" fill="#6f7d8b"/><rect x="134" y="160" width="132" height="68" rx="16" fill="#8f9ba6"/>`,
  storage: `<ellipse cx="200" cy="322" rx="130" ry="9" fill="#2a2420" opacity=".10"/><rect x="76" y="128" width="248" height="16" rx="5" fill="#d9d0bf"/><rect x="84" y="144" width="232" height="146" rx="6" fill="#ece6da"/><rect x="96" y="156" width="208" height="38" rx="4" fill="#f7f3ec"/><rect x="96" y="198" width="208" height="38" rx="4" fill="#f7f3ec"/><rect x="96" y="240" width="208" height="38" rx="4" fill="#f7f3ec"/>`,
  lamp: `<ellipse cx="200" cy="322" rx="70" ry="9" fill="#2a2420" opacity=".10"/><path d="M150 82h100l-18 72h-64z" fill="#585b5d"/><path d="M150 82h100l-4 16h-92z" fill="#3b3e40"/><rect x="197" y="154" width="6" height="150" fill="#2c2a28"/><ellipse cx="200" cy="308" rx="46" ry="9" fill="#2c2a28"/>`,
  linen: `<ellipse cx="200" cy="322" rx="130" ry="9" fill="#2a2420" opacity=".10"/><rect x="84" y="236" width="232" height="52" rx="12" fill="#90a898"/><rect x="94" y="192" width="212" height="52" rx="12" fill="#b1c2b6"/><rect x="104" y="150" width="192" height="52" rx="12" fill="#90a898"/>`,
  vase: `<ellipse cx="200" cy="322" rx="62" ry="9" fill="#2a2420" opacity=".10"/><path d="M178 112h44l-6 40c36 28 44 80 34 126-6 28-26 40-50 40s-44-12-50-40c-10-46-2-98 34-126z" fill="#b9724f"/><rect x="172" y="104" width="56" height="12" rx="5" fill="#9d5c3d"/>`,
  hero: `<ellipse cx="200" cy="322" rx="156" ry="9" fill="#2a2420" opacity=".10"/><rect x="64" y="146" width="272" height="100" rx="22" fill="#748f7e"/><rect x="40" y="192" width="56" height="106" rx="20" fill="#90a898"/><rect x="304" y="192" width="56" height="106" rx="20" fill="#90a898"/><rect x="88" y="214" width="224" height="72" rx="14" fill="#90a898"/><rect x="96" y="170" width="100" height="62" rx="14" fill="#b1c2b6"/><rect x="204" y="170" width="100" height="62" rx="14" fill="#b1c2b6"/>`,
}

export function FurnitureArt({ kind, label }: { kind: ArtKind; label: string }) {
  return (
    <svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg" role="img" aria-label={label} className="block h-full w-full">
      <g dangerouslySetInnerHTML={{ __html: art[kind] }} />
    </svg>
  )
}
