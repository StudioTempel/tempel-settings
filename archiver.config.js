import fs from "fs";
import archiver from "archiver";

const filename = `tempel-settings.zip`;

const output = fs.createWriteStream("build/" + filename);
const archive = archiver("zip", {
  zlib: { level: 9 },
});

output.on("close", function () {
  console.log(archive.pointer() + " total bytes");
  console.log(
    "archiver has been finalized and the output file descriptor has closed.",
  );
});

output.on("end", function () {
  console.log("Data has been drained");
});

archive.on("warning", function (err) {
  if (err.code === "ENOENT") {
    console.log(err);
  } else {
    throw err;
  }
});

archive.on("error", function (err) {
  throw err;
});

archive.pipe(output);

archive.directory("tempel-settings", "/");

archive.directory("dist/", "tempel-settings/dist");
archive.directory("includes/", "tempel-settings/includes");
archive.directory("vendor/", "tempel-settings/vendor");
archive.directory("languages/", "tempel-settings/languages");
archive.directory("src/", "tempel-settings/src");

archive.file("tempel.php", { name: "tempel-settings/tempel.php" });
archive.file("README.md", { name: "tempel-settings/README.md" });

archive.finalize();
