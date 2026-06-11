function importSave(file) {
  const reader = new FileReader();
  reader.onload = (e) => {
    let text = e.target.result.replace(/\0/g, "").trim();
    try {
      displaySaveData(JSON.parse(text));
    } catch (err) {
      if (typeof showToast === "function") showToast(window.SYLORA_T ? window.SYLORA_T("toast.save_corrupt") : "Invalid save file.", "error");
      console.error(err);
    }
  };
  reader.readAsText(file, "utf-8");
}

function displaySaveData(data) {
  const s = data.stats;
  console.log("Nível:", s.lvl, "| HP:", s.hp, "/", s.hp_total,
              "| XP:", s.xp, "/", s.xp_req, "| Sala:", s.save_rm);
}

function exportSave(saveData) {
  const blob = new Blob([JSON.stringify(saveData) + "\0"], { type: "application/octet-stream" });
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement("a");
  a.href = url; a.download = "syloradata.sav"; a.click();
  URL.revokeObjectURL(url);
}

window.importSave = importSave;
window.displaySaveData = displaySaveData;
window.exportSave = exportSave;
